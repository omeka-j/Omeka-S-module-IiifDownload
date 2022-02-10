<?php

namespace IiifDownload;

use Omeka\Module\AbstractModule;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\EventManager\Event;

use IiifDownload\Form\ConfigForm;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Omeka\Module\Exception\ModuleCannotInstallException;
use Omeka\Stdlib\Message;

class Module extends AbstractModule
{
    /** Module body **/

    /**
     * Get this module's configuration array.
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * getConfigForm
     *
     * 設定フォーム
     * @param  mixed $renderer
     */
    public function getConfigForm(PhpRenderer $renderer)
    {
        $translate = $renderer->plugin('translate');

        $services = $this->getServiceLocator();
        // 設定内容取得
        $settings = $services->get('Omeka\Settings');
        $form = $services->get('FormElementManager')->get(ConfigForm::class);
        $data = [
            'iiifdownload_url' => $settings->get('iiifdownload_url', ''),
            'iiifdownload_description' => $settings->get('iiifdownload_description', '')
        ];
        $form->init();
        // フォームにデータを設定する
        $form->setData($data);
        $html = $renderer->formCollection($form);
        return '<p>'
            . $translate('Please set configs of a downloader.') // @translate
            . '</p>'
            . $html;
    }

    /**
     * handleConfigForm
     *
     * 設定フォーム送信時
     * @param  mixed $controller
     */
    public function handleConfigForm(AbstractController $controller)
    {
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');
        $params = $controller->getRequest()->getPost();

        // 設定データ反映
        $settings->set('iiifdownload_url', $params["iiifdownload_url"]);
        $settings->set('iiifdownload_description', $params["iiifdownload_description"]);
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $controllers = [
            'Omeka\Controller\Site\Item',
        ];
        foreach ($controllers as $controller) {
            $sharedEventManager->attach(
                $controller,
                'view.show.after',
                [$this, 'displayIiifDownload']
            );
        }
    }

    /**
     * Display
     *
     * @param Event $event
     */
    public function displayIiifDownload(Event $event)
    {
        $view = $event->getTarget();
        $resource = $event->getTarget()->resource;
        echo $view->IiifDownload($resource);
    }

    /**
     * install
     * インストールで実行する処理
     *
     * @param ServiceLocatorInterface $services
     */
    public function install(ServiceLocatorInterface $services): void
    {
        $translator = $services->get('MvcTranslator');
        // サービスをメンバー変数に設定する
        $this->setServiceLocator($services);
        // 依存モジュールチェック
        if (!$this->checkDependencies()) {
            $message = new Message(
                $translator->translate('This module requires modules "%s".'), // @translate
                implode('", "', $this->dependencies)
            );
            throw new ModuleCannotInstallException((string) $message);
        }
        // 後処理を実行する
        $this->postInstall($services);
    }

    /**
     * 依存モジュールチェック
     *
     * @return bool
     */
    protected function checkDependencies(): bool
    {
        // モジュール設定取得
        $config = $this->getConfig();
        // 依存モジュール取得
        $this->dependencies = $config['dependencies'];
        // 依存モジュールが存在しない、または全てアクティブの場合はtrue
        return empty($this->dependencies) || $this->areModulesActive($this->dependencies);
    }

    /**
     * areModulesActive
     *
     * 依存モジュールがアクティブかどうかチェック
     * @param array $modules
     * @return bool
     */
    protected function areModulesActive(array $modules): bool
    {
        $services = $this->getServiceLocator();
        /** @var \Omeka\Module\Manager $moduleManager */
        $moduleManager = $services->get('Omeka\ModuleManager');
        foreach ($modules as $module) {
            $module = $moduleManager->getModule($module);
            // アクティブでない場合はfalse
            if (!$module || $module->getState() !== \Omeka\Module\Manager::STATE_ACTIVE) {
                return false;
            }
        }
        return true;
    }

    /**
     * postInstall
     *
     * インストール後処理
     * @param  mixed $services
     */
    protected function postInstall(ServiceLocatorInterface $services): void
    {
        // 設定追加
        $this->manageSetting('install');
    }

    /**
     * unistall
     * アンインストールで実行する処理
     *
     * @param ServiceLocatorInterface $services
     */
    public function uninstall(ServiceLocatorInterface $services): void
    {
        // 設定を削除する
        $this->manageSetting('unistall');
    }
    
    /**
     * manageSetting
     *
     * 設定を追加、削除する
     * @param [type] $type
     */
    private function manageSetting($type): void
    {
        // サービス取得
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');
        // モジュール設定取得
        $config = $this->getConfig();
        // 設定値取得
        $defaultSettings = $config['iiifdownload']['config'];
        switch ($type) {
            // インストール時の追加処理
            case 'install':
                // 設定
                $settings->set('iiifdownload_url', $defaultSettings["iiifdownload_url"]);
                $settings->set('iiifdownload_description', $defaultSettings["iiifdownload_description"]);
                break;
            case 'unistall':
                // 設定削除
                $settings->delete('iiifdownload_url');
                $settings->delete('iiifdownload_description');
                break;
        }
    }
}
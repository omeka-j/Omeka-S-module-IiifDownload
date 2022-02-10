<?php declare(strict_types=1);

namespace IiifDownload\Form;

use Laminas\EventManager\Event;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\I18n\Translator\TranslatorAwareTrait;
use Laminas\Form\Fieldset;

/**
 * ConfigForm
 * 設定フォーム
 */
class ConfigForm extends Form implements TranslatorAwareInterface
{
    use EventManagerAwareTrait;
    use TranslatorAwareTrait;

    public function init(): void
    {
        $this
            // URL
            ->add([
                'name' => 'iiifdownload_url',
                'type' => Element\Text::class,
                'options' => [
                    'label' => 'URL', // @translate
                ],
                'attributes' => [
                    'id' => 'iiifdownload_url',
                    'data-placeholder' => 'https://static.ldas.jp/viewer/iiif/downloader/?manifest=', // @translate
                ],
            ])
            
            // Description
            ->add([
                'name' => 'iiifdownload_description',
                'type' => Element\TextArea::class,
                'options' => [
                    'label' => 'Description', // @translate
                ],
                'attributes' => [
                    'id' => 'iiifdownload_description',
                    'data-placeholder' => '', // @translate
                ],
            ])
        ;
        // 以下そのまま
        $addEvent = new Event('form.add_elements', $this);
        $this->getEventManager()->triggerEvent($addEvent);

        $inputFilter = $this->getInputFilter();
        $inputFilter
            ->add([
                'name' => 'iiifdownload_url',
                'required' => true,
            ])
            ->add([
                'name' => 'iiifdownload_description',
                'required' => false,
            ])
        ;

        $filterEvent = new Event('form.add_input_filters', $this, ['inputFilter' => $inputFilter]);
        $this->getEventManager()->triggerEvent($filterEvent);
    }

    protected function translate($args)
    {
        $translator = $this->getTranslator();
        return $translator->translate($args);
    }
}

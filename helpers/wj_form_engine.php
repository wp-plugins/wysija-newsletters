<?php
defined('WYSIJA') or die('Restricted access');

class WYSIJA_help_wj_form_engine extends WYSIJA_object {

    var $_debug = false;

    var $_context = 'editor';

    var $_data = null;
    var $_styles = null;

    var $TEXT_SIZES = array(8, 9, 10, 11, 12, 13, 14, 16, 18, 24, 36, 48, 72);
    var $TITLE_SIZES = array(16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 40, 44, 48, 54, 60, 66, 72);
    var $FONTS = array("Arial", "Arial Black", "Comic Sans MS", "Courier New", "Georgia", "Impact", "Tahoma", "Times New Roman", "Trebuchet MS", "Verdana");
    
    function WYSIJA_help_wj_form_engine(){ }
    
    function getTranslations() {
        return array(
            'editSettings' => __('Edit', WYSIJAFUTURE)
        );
    }
    
    function getData($type = null) {
        if($type !== null) {
            if(array_key_exists($type, $this->_data)) {
                return $this->_data[$type];
            } else {

                $defaults = $this->getDefaultData();
                return $defaults[$type];
            }
        }
        return $this->_data;
    }
    function setData($value = null, $decode = false) {
        if(!$value) {
            $this->_data = $this->getDefaultData();
        } else {
            $this->_data = $value;
            if($decode) {
                $this->_data = $this->getDecoded('data');
            }
        }
    }
    function getDefaultData() {
        return array(
            'name' => __('Default form', WYSIJAFUTURE),
            'blocks' => array(
                array(
                    'type' => 'input',
                    'field' => 'email',
                    'id' => 'wysija-email',
                    'name' => 'wysija-email-field',
                    'static' => true,
                    'params' => array(
                        'label' => __('Email', WYSIJAFUTURE),
                        'validate' => true,
                        'validation_error' => __('Invalid email address',WYSIJAFUTURE)
                    )
                ),
                array(
                    'type' => 'input',
                    'field' => 'firstname',
                    'id' => 'wysija-firstname',
                    'name' => 'wysija-firstname-field',
                    'params' => array(
                        'label' => __('Firstname', WYSIJAFUTURE),
                        'validate' => false
                    )
                ),
                array(
                    'type' => 'input',
                    'field' => 'lastname',
                    'id' => 'wysija-lastname',
                    'name' => 'wysija-lastname-field',
                    'params' => array(
                        'label' => __('Lastname', WYSIJAFUTURE),
                        'validate' => false
                    )
                ),
                array(
                    'type' => 'submit',
                    'id' => 'wysija-submit',
                    'name' => 'wysija-submit-field',
                    'static' => true,
                    'params' => array(
                        'label' => __('Subscribe!', WYSIJAFUTURE),
                        'validate' => false
                    )
                )
            )
        );
    }
    
    function getStyles($keys = null) {
        if($keys === null) return $this->_styles;
        if(!is_array($keys)) {
            $keys = array($keys);
        }
        $output = array();
        for($i=0; $i<count($keys);$i++) {
            if(isset($this->_styles[$keys[$i]])) {
                $output = array_merge($output, $this->_styles[$keys[$i]]);
            }
        }
        return $output;
    }
    function getStyle($key, $subkey) {
        $styles = $this->getStyles($key);
        return $styles[$subkey];
    }
    function setStyles($value = null, $decode = false) {
        if(!$value) {
            $this->_styles = $this->getDefaultStyles();
        } else {
            $this->_styles = $value;
            if($decode) {
                $this->_styles = $this->getDecoded('styles');
            }
        }
    }
    function getDefaultStyles() {
        return array(
            'html' => array(
                'background' => 'FFFFFF'
            ),
            'body' => array(
                'color' => '000000',
                'family' => 'Arial',
                'size' => $this->TEXT_SIZES[5],
                'background' => 'FFFFFF'
            ),
            'h1' => array(
                'color' => '000000',
                'family' => 'Arial',
                'size' => $this->TITLE_SIZES[6]
            ),
            'h2' => array(
                'color' => '000000',
                'family' => 'Arial',
                'size' => $this->TITLE_SIZES[5]
            ),
            'h3' => array(
                'color' => '000000',
                'family' => 'Arial',
                'size' => $this->TITLE_SIZES[4]
            ),
            'a' => array(
                'color' => '0000FF',
                'underline' => false
            )
        );
    }
    
    function renderEditor() {
        $this->setContext('editor');
        if($this->isDataValid() === false) {
            throw new Exception('data is not valid');
        } else {
            $hParser =& WYSIJA::get('wj_parser', 'helper');
            $hParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
            $data = array(
                'body' => $this->renderEditorBody(),
                'is_debug' => $this->isDebug(),
                'i18n' => $this->getTranslations()
            );
            return $hParser->render($data, 'templates/form/editor_template.html');
        }
    }
    function renderEditorBody() {
        $hParser =& WYSIJA::get('wj_parser', 'helper');
        $hParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        $blocks = $this->getData('blocks');
        if(empty($blocks)) return '';
        $body = '';
        foreach($blocks as $key => $block) {

            $data = array_merge($block, array('i18n' => $this->getTranslations()));
            $body .= $hParser->render($data, 'templates/form/block_template.html');
        }
        return $body;
    }
    function renderEditorBlock($block = array()) {
        $hParser =& WYSIJA::get('wj_parser', 'helper');
        $hParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        $hParser->setStripSpecialchars(true);
        $block['i18n'] = $this->getTranslations();
        return $hParser->render($block, 'templates/form/block_'.$block['type'].'.html');
    }
    
    function renderImages($data = array()) {
        $hParser =& WYSIJA::get('wj_parser', 'helper');
        $hParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        return $hParser->render(array('images' => $data), 'templates/form/toolbar/images.html');
    }
    function extractStyles($raw) {
        $rules = explode(';', $raw);
        $output = array();
        foreach($rules as $rule) {
            $sub_property = false;
            $combo = explode(':', $rule);
            if(count($combo) === 2) {
                list($property, $value) = $combo;

                $property = trim($property);
                $value = trim($value);
            } else {
                continue;
            }
            switch($property) {
                case 'background':
                case 'background-color':
                    $property = 'background';
                case 'color':

                    $value = str_replace('#', '', $value);

                    if(strlen($value) === 3) {
                        $value = sprintf('%s%s%s%s%s%s', substr($value, 0, 1), substr($value, 0, 1), substr($value, 1, 1), substr($value, 1, 1), substr($value, 2, 1), substr($value, 2, 1));
                    }
                    break;
                case 'font-family':
                    $property = 'family';
                    $value = array_shift(explode(',', $value));
                    break;
                case 'font-size':
                    $property = 'size';
                case 'height':
                    $value = (int)$value;
                    break;
                case 'text-decoration':
                    $property = 'underline';
                    $value = ($value === 'none') ? '-1' : '1';
                    break;
                case 'border-color':

                    $value = str_replace('#', '', $value);

                    if(strlen($value) === 3) {
                        $value = sprintf('%s%s%s%s%s%s', substr($value, 0, 1), substr($value, 0, 1), substr($value, 1, 1), substr($value, 1, 1), substr($value, 2, 1), substr($value, 2, 1));
                    }
                    list($property, $sub_property) = explode('-', $property);
                    break;
                case 'border-size':
                    $value = (int)$value;
                    list($property, $sub_property) = explode('-', $property);
                    break;
                case 'border-style':
                    list($property, $sub_property) = explode('-', $property);
                    break;
            }
            if($sub_property !== FALSE) {
                $output[$property][$sub_property] = $value;
            } else {
                $output[$property] = $value;
            }
        }
        return $output;
    }
    
    function renderStylesBar() {
        $this->setContext('editor');
        $hParser =& WYSIJA::get('wj_parser', 'helper');
        $hParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        $hParser->setStripSpecialchars(true);
        $data = $this->getStyles();
        $data['i18n'] = $this->getTranslations();
        $data['TEXT_SIZES'] = $this->TEXT_SIZES;
        $data['VIEWBROWSER_SIZES'] = $this->VIEWBROWSER_SIZES;
        $data['TITLE_SIZES'] = $this->TITLE_SIZES;
        $data['FONTS'] = $this->FONTS;
        return $hParser->render($data, 'templates/form/toolbar/styles.html');
    }
    function formatStyles($styles = array()) {
        if(empty($styles)) return;
        $data = array();
        foreach($styles as $style => $value) {
            $stylesArray = explode('-', $style);
            if(count($stylesArray) === 2) {
                $data[$stylesArray[0]][$stylesArray[1]] = $value;
            } else if(count($stylesArray) === 3) {

                if($stylesArray[2] === 'transparent') {
                    $data[$stylesArray[0]][$stylesArray[1]] = $stylesArray[2];
                } else {
                    $data[$stylesArray[0]][$stylesArray[1]][$stylesArray[2]] = $value;
                }
            }
        }
        return $data;
    }
    function getContext() {
        return $this->_context;
    }
    function setContext($value = null) {
        if($value !== null) $this->_context = $value;
    }
    function isDebug() {
        return ($this->_debug === true);
    }
    function getEncoded($type = 'data') {
        return base64_encode(serialize($this->{'get'.ucfirst($type)}()));
    }
    function getDecoded($type = 'data') {
        return unserialize(base64_decode($this->{'get'.ucfirst($type)}()));
    }
    
    function isDataValid() {
        return ($this->getData() !== null);
    }
    
    function renderStyles() {
        $hParser =& WYSIJA::get('wj_parser', 'helper');
        $hParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        $hParser->setStripSpecialchars(true);
        $hParser->setInline(true);
        $data = $this->getStyles();
        $data['context'] = $this->getContext();
        switch($data['context']) {
            case 'editor':
                $hParser->setStripSpecialchars(false);
                $data['viewbrowser_container'] = '#wysija_viewbrowser';
                $data['wysija_container'] = '#wysija_wrapper';
                $data['header_container'] = '#wysija_header';
                $data['body_container'] = '#wysija_body';
                $data['text_container'] = '.editable';
                $data['footer_container'] = '#wysija_footer';
                $data['placeholder_container'] = '#wysija_block_placeholder';
                $data['unsubscribe_container'] = '#wysija_unsubscribe';
            break;
            case 'email':
                $hParser->setStripSpecialchars(true);
                $data['viewbrowser_container'] = '#wysija_viewbrowser';
                $data['wysija_container'] = '#wysija_wrapper';
                $data['header_container'] = '#wysija_header_content';
                $data['body_container'] = '#wysija_body_content';
                $data['footer_container'] = '#wysija_footer_content';
                $data['text_container'] = '.wysija-text-container';
                $data['unsubscribe_container'] = '#wysija_unsubscribe';

                if(function_exists('is_rtl')) {
                    $data['is_rtl'] = is_rtl();
                } else {
                    $data['is_rtl'] = false;
                }
            break;
        }
        return $hParser->render($data, 'styles/css-'.$data['context'].'.html');
    }
    
    function applyInlineStyles($area, $block, $extra = array()) {
        $hParser =& WYSIJA::get('wj_parser', 'helper');
        $hParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        $hParser->setInline(true);
        $tags = array();
        $classes = array();
        switch($area) {
            case 'header':
            case 'footer':
                $classes = array(
                    'wysija-image-container alone-left' => array('margin' => '0', 'padding' => '0'),
                    'wysija-image-container alone-center' => array('margin' => '0 auto 0 auto', 'padding' => '0', 'text-align' => 'center'),
                    'wysija-image-container alone-right' => array('margin' => '0', 'padding' => '0')
                );
            break;
            case 'body':

                $block = preg_replace_callback('#(<h([1|2|3])[^>]*>(.*)<\/h[1|2|3]>)#Ui',
                    create_function('$matches', '$class = \'h\'.(int)$matches[2].\'-link\'; return str_replace(\'<a\', \'<a class="\'.$class.\'"\', $matches[0]);'),
                    $block);
                $tags = array(
                    'h1' => array_merge($this->getStyles('h1'), array('word-wrap' => true, 'padding' => '0', 'margin' => '0 0 10px 0', 'font-weight' => 'normal', 'line-height' => '1.3em')),
                    'h2' => array_merge($this->getStyles('h2'), array('word-wrap' => true, 'padding' => '0', 'margin' => '0 0 10px 0', 'font-weight' => 'normal', 'line-height' => '1.2em')),
                    'h3' => array_merge($this->getStyles('h3'), array('word-wrap' => true, 'padding' => '0', 'margin' => '0 0 10px 0', 'font-weight' => 'normal', 'line-height' => '1.1em')),
                    'p' => array_merge($this->getStyles('body'), array('word-wrap' => true, 'padding' => '3px 0 0 0', 'margin' => '0 0 1em 0', 'line-height' => '1.5em', 'vertical-align' => 'top')),
                    'a' => array_merge($this->getStyles('body'), $this->getStyles('a')),
                    'ul' => array('line-height' => '1.5em', 'margin' => '0 0 1em 0', 'padding' => '0'),
                    'ol' => array('line-height' => '1.5em', 'margin' => '0 0 1em 0', 'padding' => '0'),
                    'li' => array_merge($this->getStyles('body'), array('font-weight' => 'normal', 'list-type' => 'none', 'list-style-type' => 'disc', 'margin' => '0 0 0.7em 30px', 'padding' => '0'))
                );
                $classes = array(
                    'wysija-image-container alone-left' => array('margin' => '0', 'padding' => '0'),
                    'wysija-image-container alone-center' => array('margin' => '1em auto 1em auto', 'padding' => '0', 'text-align' => 'center'),
                    'wysija-image-container alone-right' => array('margin' => '0', 'padding' => '0'),
                    'wysija-image-left' => array('vertical-align' => 'top'),
                    'wysija-image-center' => array('margin' => '0 auto 0 auto', 'vertical-align' => 'top'),
                    'wysija-image-right' => array('vertical-align' => 'top'),
                    'wysija-image-container align-left' => array('float' => 'left', 'margin' => '0', 'padding' => '0'),
                    'wysija-image-container align-center' => array('margin' => '0 auto 0 auto', 'text-align' => 'center', 'padding' => '0'),
                    'wysija-image-container align-right' => array('float' => 'right', 'margin' => '0', 'padding' => '0'),
                    'wysija-divider-container' => array('margin' => '0 auto 0 auto', 'padding' => '0', 'text-align' => 'center'),
                    'h1-link' => array_merge($this->getStyles('h1'), $this->getStyles('a')),
                    'h2-link' => array_merge($this->getStyles('h2'), $this->getStyles('a')),
                    'h3-link' => array_merge($this->getStyles('h3'), $this->getStyles('a')),
                    'align-left' => array('text-align' => 'left'),
                    'align-center' => array('text-align' => 'center'),
                    'align-right' => array('text-align' => 'right'),
                    'align-justify' => array('text-align' => 'justify')
                );

                if(array_key_exists('background_color', $extra) and $extra['background_color'] !== null) {
                    $tags['p']['background'] = $extra['background_color'];
                    $tags['a']['background'] = $extra['background_color'];
                    $tags['ul']['background'] = $extra['background_color'];
                    $tags['li']['background'] = $extra['background_color'];
                }
            break;
            case 'unsubscribe':
                $tags = array(
                    'a' => $this->getStyles('unsubscribe')
                );
            break;
            case 'viewbrowser':
                $tags = array(
                    'a' => $this->getStyles('viewbrowser')
                );
            break;
        }
        if(empty($tags) === FALSE) {
            foreach($tags as $tag => $styles) {
                $styles = $this->splitSpacing($styles);
                $inlineStyles = $hParser->render(array_merge($styles, array('tag' => $tag)), 'styles/inline.html');
                $inlineStyles = preg_replace('/(\n*)/', '', $inlineStyles);
                $tags['#< *'.$tag.'((?:(?!style).)*)>#Ui'] = '<'.$tag.' style="'.$inlineStyles.'"$1>';
                unset($tags[$tag]);
            }
            $block = preg_replace(array_keys($tags), $tags, $block);
        }
        if(empty($classes) === FALSE) {
            foreach($classes as $class => $styles) {

                $styles = $this->splitSpacing($styles);
                $inlineStyles = $hParser->render($styles, 'styles/inline.html');
                $inlineStyles = preg_replace('/(\n*)/', '', $inlineStyles);
                if(in_array($class, array('h1-link', 'h2-link', 'h3-link'))) {
                    $classes['#<([^ /]+) ((?:(?!>|style).)*)(?:style="([^"]*)")?((?:(?!>|style).)*)class="[^"]*'.$class.'[^"]*"((?:(?!>|style).)*)(?:style="([^"]*)")?((?:(?!>|style).)*)>#Ui'] = '<$1 $2$4$5$7 style="'.$inlineStyles.'">';
                } else {
                    $classes['#<([^ /]+) ((?:(?!>|style).)*)(?:style="([^"]*)")?((?:(?!>|style).)*)class="[^"]*'.$class.'[^"]*"((?:(?!>|style).)*)(?:style="([^"]*)")?((?:(?!>|style).)*)>#Ui'] = '<$1 $2$4$5$7 style="$3$6'.$inlineStyles.'">';
                }
                unset($classes[$class]);
            }
            $styledBlock = preg_replace(array_keys($classes), $classes, $block);
            
            if(strlen(trim($styledBlock)) > 0) {
                $block = $styledBlock;
            }
        }
        return $block;
    }
    function splitSpacing($styles) {
        foreach($styles as $property => $value) {
            if($property === 'margin' or $property === 'padding') {

                $values = explode(' ', $value);

                switch(count($values)) {
                    case 1:
                        $styles[$property.'-top'] = $values[0];
                        $styles[$property.'-right'] = $values[0];
                        $styles[$property.'-bottom'] = $values[0];
                        $styles[$property.'-left'] = $values[0];
                    break;
                    case 2:
                        $styles[$property.'-top'] = $values[0];
                        $styles[$property.'-right'] = $values[1];
                        $styles[$property.'-bottom'] = $values[0];
                        $styles[$property.'-left'] = $values[1];
                    break;
                    case 4:
                        $styles[$property.'-top'] = $values[0];
                        $styles[$property.'-right'] = $values[1];
                        $styles[$property.'-bottom'] = $values[2];
                        $styles[$property.'-left'] = $values[3];
                    break;
                }

                unset($styles[$property]);
            }
        }
        return $styles;
    }
    function formatColor($color) {
        if(strlen(trim($color)) === 0 or $color === 'transparent') {
            return 'transparent';
        } else {
            return '#'.$color;
        }
    }
}

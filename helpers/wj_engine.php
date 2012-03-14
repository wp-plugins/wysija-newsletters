<?php
defined('WYSIJA') or die('Restricted access');

class WYSIJA_help_wj_engine extends WYSIJA_object {
    var $_context = 'editor';

    var $_data = null;
    var $_styles = null;

    var $TEXT_SIZES = array(8, 9, 10, 11, 12, 13, 14, 16, 18, 24, 36, 48, 72);
    var $TITLE_SIZES = array(16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 40, 44, 48, 54, 60, 66, 72);
    var $FONTS = array("Arial", "Arial Black", "Comic Sans MS", "Courier New", "Georgia", "Impact", "Tahoma", "Times New Roman", "Trebuchet MS", "Verdana");
    
    function WYSIJA_help_wj_engine(){ }
    
    function getTranslations() {
        return array(
            'dropHeaderNotice' => __('Drop your logo in this header.',WYSIJA),
            'dropFooterNotice' => __('Drop your footer image here.',WYSIJA),
            'dropBannerNotice' => __('If you leave this area empty, it will not display once you send your e-mail',WYSIJA),
            'clickToEditText' => __('Click here to add a title or text.', WYSIJA),
            'alignmentLeft' =>  __('Align left',WYSIJA),
            'alignmentCenter' => __('Align center',WYSIJA),
            'alignmentRight' => __('Align right',WYSIJA),
            'addImageLink' => __('Add link / Alternative text',WYSIJA),
            'removeImageLink' => __('Remove link',WYSIJA),
            'removeImage' => __('Remove image',WYSIJA),
            'remove' => __('Remove', WYSIJA),
            'editText' => __( 'Edit text',WYSIJA),
            'removeText' => __('Remove text',WYSIJA),
            'textLabel' => __('Plain text',WYSIJA),
            'dividerLabel' => __('Horizontal line',WYSIJA),
            'customDividerLabel' => __('Custom horizontal line',WYSIJA),
            'postLabel' => __('WordPress post',WYSIJA),
            'styleBodyLabel' => __('Text',WYSIJA),
            'styleH1Label' => __('Heading 1',WYSIJA),
            'styleH2Label' => __('Heading 2',WYSIJA),
            'styleH3Label' => __('Heading 3',WYSIJA),
            'styleLinksLabel' => __('Links',WYSIJA),
            'styleLinksDecorationLabel' => __('Underline links',WYSIJA),
            'styleFooterLabel' => __('Footer text',WYSIJA),
            'styleFooterBackgroundLabel' => __('Footer background',WYSIJA),
            'styleBodyBackgroundLabel' => __('Newsletter color',WYSIJA),
            'styleHtmlBackgroundLabel' => __('Background color', WYSIJA),
            'styleHeaderBackgroundLabel' => __('Header background color', WYSIJA),
            'styleDividerLabel' => __('Horizontal line',WYSIJA),
            'styleUnsubscribeColorLabel' => __('Unsubscribe color',WYSIJA),
            'articleSelectionTitle' => __('Article Selection', WYSIJA),
            'bookmarkSelectionTitle' => __('Social Bookmark Selection', WYSIJA),
            'dividerSelectionTitle' => __('Divider Selection', WYSIJA),
            'abouttodeletetheme' => __('You are about to delete the theme : %1$s. Do you really want to do that?', WYSIJA),
            'addLinkTitle' => __('Add Link & Alternative text', WYSIJA),
            'styleTransparent' => __('Check this box if you want transparency', WYSIJA)
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
        $dividersHelper =& WYSIJA::get('dividers', 'helper');
        return array(
            'header' => array(
                'alignment' => 'center',
                'type' => 'header',
                'static' => '1',
                'text' => null,
                'image' => array(
                    'src' => null,
                    'width' => 600,
                    'height' => 86,
                    'url' => null,
                    'alignment' => 'center',
                    'static' => '1'
                )
            ),
            'body' => array(),
            'footer' => array(
                'alignment' => 'center',
                'type' => 'footer',
                'static' => '1',
                'text' => null,
                'image' => array(
                    'src' => null,
                    'width' => 600,
                    'height' => 86,
                    'url' => null,
                    'alignment' => 'center',
                    'static' => '1'
                )
            ),
            'widgets' => array(
                'divider' => array_merge($dividersHelper->getDefault(), array('type' => 'divider'))
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
            'header' => array(
                'background' => 'FFFFFF'
            ),
            'body' => array(
                'color' => '000000',
                'family' => 'Arial',
                'size' => $this->TEXT_SIZES[5],
                'background' => 'FFFFFF'
            ),
            'footer' => array(
                'color' => '000000',
                'family' => 'Arial',
                'size' => $this->TEXT_SIZES[5],
                'background' => 'cccccc'
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
            ),
            'unsubscribe' => array(
                'color' => '000000'
            )
        );
    }
    
    function renderEditor() {
        $this->setContext('editor');
        if($this->isDataValid() === false) {
            throw new Exception('data is not valid');
        } else {
            $wjParser =& WYSIJA::get('wj_parser', 'helper');
            $wjParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);

            $config=&WYSIJA::get("config","model");

            $modelUser =& WYSIJA::get("user","model");
            $data = array(
                'header' => $this->renderEditorHeader(),
                'body' => $this->renderEditorBody(),
                'footer' => $this->renderEditorFooter(),
                'unsubscribe_link' => $modelUser->getUnsubLink(),
                'company_address' => nl2br($config->getValue('company_address'))
            );
            return $wjParser->render($data, 'templates/editor/editor_template.html');
        }
    }
    function renderEditorHeader($data = null) {
        $wjParser =& WYSIJA::get('wj_parser', 'helper');
        $wjParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        $wjParser->setStripSpecialchars(true);
        if($data !== null) {
            $block = $data;
        } else {
            $block = $this->getData('header');
        }
        $data = array_merge($block, array('i18n' => $this->getTranslations()));
        return $wjParser->render($data, 'templates/editor/header_template.html');
    }
    function renderEditorBody() {
        $wjParser =& WYSIJA::get('wj_parser', 'helper');
        $wjParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        $blocks = $this->getData('body');
        if(empty($blocks)) return '';
        $body = '';
        foreach($blocks as $key => $block) {

            $data = array_merge($block, array('i18n' => $this->getTranslations()));
            $body .= $wjParser->render($data, 'templates/editor/block_template.html');
        }
        return $body;
    }
    function renderEditorFooter($data = null)
    {
        $wjParser =& WYSIJA::get('wj_parser', 'helper');
        $wjParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        if($data !== null) {
            $block = $data;
        } else {
            $block = $this->getData('footer');
        }
        $data = array_merge($block, array('i18n' => $this->getTranslations()));
        return $wjParser->render($data, 'templates/editor/footer_template.html');
    }
    function renderEditorBlock($block = array()) {
        $wjParser =& WYSIJA::get('wj_parser', 'helper');
        $wjParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        $wjParser->setStripSpecialchars(true);
        $block['i18n'] = $this->getTranslations();
        return $wjParser->render($block, 'templates/editor/block_'.$block['type'].'.html');
    }
    
    function renderImages($data = array()) {
        $wjParser =& WYSIJA::get('wj_parser', 'helper');
        $wjParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        return $wjParser->render(array('images' => $data), 'templates/toolbar/images.html');
    }
    
    function renderThemes() {
        $themes = array();
        $themeHelper =& WYSIJA::get('themes', 'helper');
        $installed = $themeHelper->getInstalled();
        if(empty($installed)) {
            return '';
        } else {
            foreach($installed as $theme) {
                $themes[] = $themeHelper->getInformation($theme);
            }
        }
        $wjParser =& WYSIJA::get('wj_parser', 'helper');
        $wjParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        return $wjParser->render(array('themes' => $themes), 'templates/toolbar/themes.html');
    }
    function renderThemeStyles($theme = 'default') {
        $this->setContext('editor');
        $themeHelper =& WYSIJA::get('themes', 'helper');
        $stylesheet = $themeHelper->getStylesheet($theme);
        if($stylesheet === NULL) {

            $this->setStyles(null);
        } else {

            $styles = array();
            $defaults = $this->getDefaultStyles();

            foreach($defaults as $tag => $values) {

                preg_match('/\.?'.$tag.'\s?{(.+)}/Ui', $stylesheet, $matches);
                if(isset($matches[1])) {

                    $styles[$tag] = $this->extractStyles($matches[1]);
                }
            }
            $this->setStyles($styles);
        }
        return array(
            'css' => $this->renderStyles(),
            'form' => $this->renderStylesBar()
        );
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
    function renderTheme($theme = 'default') {
        $output = array(
            'header' => null,
            'footer' => null,
            'divider' => null
        );
        $themeHelper =& WYSIJA::get('themes', 'helper');
        $data = $themeHelper->getData($theme);
        if($data['header'] !== NULL) {
            $output['header'] = $this->renderEditorHeader($data['header']);
        }
        if($data['footer'] !== NULL) {
            $output['footer'] = $this->renderEditorFooter($data['footer']);
        }
        if($data['divider'] !== NULL) {
            $output['divider'] = $this->renderEditorBlock(array_merge(array('no-block' => true), $data['divider']));
            $output['divider_options'] = $data['divider'];
        }
        return $output;
    }
    
    function renderStylesBar() {
        $this->setContext('editor');
        $wjParser =& WYSIJA::get('wj_parser', 'helper');
        $wjParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        $wjParser->setStripSpecialchars(true);
        $data = $this->getStyles();
        $data['i18n'] = $this->getTranslations();
        $data['TEXT_SIZES'] = $this->TEXT_SIZES;
        $data['TITLE_SIZES'] = $this->TITLE_SIZES;
        $data['FONTS'] = $this->FONTS;
        return $wjParser->render($data, 'templates/toolbar/styles.html');
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
        $wjParser =& WYSIJA::get('wj_parser', 'helper');
        $wjParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        $data = $this->getStyles();
        $data['context'] = $this->getContext();
        switch($data['context']) {
            case 'editor':
                $wjParser->setStripSpecialchars(false);
                $data['wysija_container'] = '#wysija_wrapper';
                $data['header_container'] = '#wysija_header';
                $data['body_container'] = '#wysija_body';
                $data['text_container'] = '.editable';
                $data['footer_container'] = '#wysija_footer';
                $data['placeholder_container'] = '#wysija_block_placeholder';
                $data['unsubscribe_container'] = '#wysija_unsubscribe';
            break;
            case 'email':
                $wjParser->setStripSpecialchars(true);
                $data['wysija_container'] = '#wysija_wrapper';
                $data['header_container'] = '#wysija_header_content';
                $data['body_container'] = '#wysija_body_content';
                $data['footer_container'] = '#wysija_footer_content';
                $data['text_container'] = '.wysija-text-container';
                $data['unsubscribe_container'] = '#wysija_unsubscribe';
            break;
        }
        return $wjParser->render($data, 'styles/css-'.$data['context'].'.html');
    }
    
    function renderEmail($subject = NULL) {

        @ini_set('pcre.backtrack_limit', 1000000);
        $this->setContext('email');
        if($this->isDataValid() === false) {
            throw new Exception('data is not valid');
        } else {

            $data = array(
                'header' => $this->renderEmailHeader(),
                'body' => $this->renderEmailBody(),
                'footer' => $this->renderEmailFooter(),
                'unsubscribe' => $this->renderEmailUnsubscribe(),
                'css' => $this->renderStyles(),
                'styles' => $this->getStyles(),
            );
            $wjParser =& WYSIJA::get('wj_parser', 'helper');
            $wjParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
            $wjParser->setStripSpecialchars(true);
            $wjParser->setInline(true);

            if($subject !== NULL) {
                $data['subject'] = $subject;
            }
            try {
                $template = $wjParser->render($data, 'templates/email/email_template.html');
                return $template;
            } catch(Exception $e) {
                return '';
            }
        }
    }
    function renderEmailUnsubscribe() {
        $wjParser =& WYSIJA::get('wj_parser', 'helper');
        $wjParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        $wjParser->setStripSpecialchars(true);
        $config =& WYSIJA::get('config','model');
        $data = array(
            'unsubscribe_label' => '[unsubscribe_linklabel]',
            'company_address' => nl2br($config->getValue('company_address'))
        );

        $unsubscribe = $wjParser->render($data, 'templates/email/unsubscribe_template.html');

        $unsubscribe = $this->applyInlineStyles('unsubscribe', $unsubscribe);
        return $unsubscribe;
    }
    function renderEmailHeader() {
        $wjParser =& WYSIJA::get('wj_parser', 'helper');
        $wjParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        $wjParser->setStripSpecialchars(true);
        $data = $this->getData('header');

        if($data['text'] === NULL and $data['image']['static'] === TRUE) {
            return NULL;
        }

        $data['block_width'] = 600;

        $header = $wjParser->render($data, 'templates/email/header_template.html');

        $header = $this->applyInlineStyles('header', $header);
        return $header;
    }
    function renderEmailBody() {
        $wjParser =& WYSIJA::get('wj_parser', 'helper');
        $wjParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        $wjParser->setStripSpecialchars(true);
        $blocks = $this->getData('body');
        $body = '';
        foreach($blocks as $key => $block) {

            $block['block_width'] = 564;

            $block = $wjParser->render($block, 'templates/email/block_template.html');

            $block = $this->applyInlineStyles('body', $block);

            $body .= $block;
        }
        return $body;
    }
    function renderEmailFooter() {
        $wjParser =& WYSIJA::get('wj_parser', 'helper');
        $wjParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        $wjParser->setStripSpecialchars(true);
        $data = $this->getData('footer');

        if($data['text'] === NULL and $data['image']['static'] === TRUE) {
            return NULL;
        }

        $data['block_width'] = 600;

        $footer = $wjParser->render($data, 'templates/email/footer_template.html');

        $footer = $this->applyInlineStyles('footer', $footer);
        return $footer;
    }
    
    function applyInlineStyles($area, $block) {
        $wjParser =& WYSIJA::get('wj_parser', 'helper');
        $wjParser->setTemplatePath(WYSIJA_EDITOR_TOOLS);
        $wjParser->setInline(true);
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
                $tags = array(
                    'h1' => array_merge($this->getStyles('h1'), array('padding' => '0', 'margin' => '0 0 10px 0', 'font-weight' => 'normal', 'line-height' => '1.3em')),
                    'h2' => array_merge($this->getStyles('h2'), array('padding' => '0', 'margin' => '0 0 10px 0', 'font-weight' => 'normal', 'line-height' => '1.2em')),
                    'h3' => array_merge($this->getStyles('h3'), array('padding' => '0', 'margin' => '0 0 10px 0', 'font-weight' => 'normal', 'line-height' => '1.1em')),
                    'p' => array_merge($this->getStyles('body'), array('padding' => '3px 0 0 0', 'margin' => '0 0 1.3em 0', 'line-height' => '1.5em', 'vertical-align' => 'top')),
                    'a' => array_merge($this->getStyles('body'), $this->getStyles('a')),
                    'ul' => array('line-height' => '1.5em', 'margin' => '0 0 1em 0', 'padding' => '0'),
                    'ol' => array('line-height' => '1.5em', 'margin' => '0 0 1em 0', 'padding' => '0'),
                    'li' => array_merge($this->getStyles('body'), array('font-weight' => 'normal', 'list-type' => 'none', 'list-style-type' => 'disc', 'margin' => '0 0 0.7em 30px', 'padding' => '0'))
                );
                $classes = array(
                    'wysija-image-container alone-left' => array('margin' => '0', 'padding' => '0'),
                    'wysija-image-container alone-center' => array('margin' => '0 auto 1.1em auto', 'padding' => '0', 'text-align' => 'center'),
                    'wysija-image-container alone-right' => array('margin' => '0', 'padding' => '0'),
                    'wysija-image-center' => array('margin' => '0 auto 0 auto'),
                    'wysija-image-container align-left' => array('float' => 'left', 'margin' => '4px 15px 1.1em 0', 'padding' => '0'),
                    'wysija-image-container align-center' => array('margin' => '0 auto 1.1em auto', 'text-align' => 'center', 'padding' => '0'),
                    'wysija-image-container align-right' => array('float' => 'right', 'margin' => '4px 0 1.1em 15px', 'padding' => '0'),
                    'wysija-divider-container' => array('margin' => '0 auto 1.1em auto', 'padding' => '0', 'text-align' => 'center'),
                    'align-left' => array('text-align' => 'left'),
                    'align-center' => array('text-align' => 'center'),
                    'align-right' => array('text-align' => 'right')
                );
            break;
            case 'unsubscribe':
                $tags = array(
                    'a' => $this->getStyles('unsubscribe')
                );
            break;
        }
        if(empty($tags) === FALSE) {
            foreach($tags as $tag => $styles) {
                $styles = $this->splitSpacing($styles);
                $tags['#< *'.$tag.'((?:(?!style).)*)>#Ui'] = '<'.$tag.' style="'.$wjParser->render(array_merge($styles, array('tag' => $tag)), 'styles/inline.html').'"$1>';
                unset($tags[$tag]);
            }
            $block = preg_replace(array_keys($tags), $tags, $block);
        }
        if(empty($classes) === FALSE) {
            foreach($classes as $class => $styles) {

                $styles = $this->splitSpacing($styles);
                $classes['#<([^ /]+) ((?:(?!>|style).)*)(?:style="([^"]*)")?((?:(?!>|style).)*)class="'.$class.'"((?:(?!>|style).)*)(?:style="([^"]*)")?((?:(?!>|style).)*)>#Ui'] = '<$1 $2$4$5$7 style="$3$6'.$wjParser->render($styles, 'styles/inline.html').'">';
                unset($classes[$class]);
            }
            $block = preg_replace(array_keys($classes), $classes, $block);
        }

        if($area === 'body') {


            $block = preg_replace('#<\/p>#Ui', "<!--[if gte mso 9]></p><![endif]--></p>", $block);
            $block = preg_replace('#<p(.*)>#Ui', "\n<p$1><!--[if gte mso 9]></p><p class=\"wysija-fix-paragraph\"><![endif]-->", $block);

            $block = preg_replace('#<\/h2>#Ui', "<!--[if gte mso 9]></h2><![endif]--></h2>", $block);
            $block = preg_replace('#<h2(.*)>#Ui', "<h2$1><!--[if gte mso 9]></h2><h2 class=\"wysija-fix-h2\"><![endif]-->", $block);

            $block = preg_replace('#<\/h3>#Ui', "<!--[if gte mso 9]></h3><![endif]--></h3>", $block);
            $block = preg_replace('#<h3(.*)>#Ui', "<h3$1><!--[if gte mso 9]></h3><h3 class=\"wysija-fix-h3\"><![endif]-->", $block);

            $block = preg_replace('#<ol(.*)>#Ui', "\n<ul$1>", $block);
            $block = preg_replace('#<ul(.*)>#Ui', "\n<ul$1>", $block);
            $block = preg_replace('#<li(.*)>#Ui', "\n<li$1>", $block);
            $pFixStyles = $this->splitSpacing(array_merge($this->getStyles('body'), array('padding' => '3px 0 0 0', 'margin' => '0 0 1.3em 0', 'line-height' => '1em', 'vertical-align' => 'top')));
            $h2FixStyles = $this->splitSpacing(array_merge($this->getStyles('h2'), array('padding' => '0', 'margin' => '0 0 10px 0', 'font-weight' => 'normal', 'line-height' => '1em')));
            $h3FixStyles = $this->splitSpacing(array_merge($this->getStyles('h3'), array('padding' => '0', 'margin' => '0 0 10px 0', 'font-weight' => 'normal', 'line-height' => '1em')));
            $block = str_replace('class="wysija-fix-paragraph"', 'style="'.$wjParser->render($pFixStyles, 'styles/inline.html').'"', $block);
            $block = str_replace('class="wysija-fix-h2"', 'style="'.$wjParser->render($h2FixStyles, 'styles/inline.html').'"', $block);
            $block = str_replace('class="wysija-fix-h3"', 'style="'.$wjParser->render($h3FixStyles, 'styles/inline.html').'"', $block);
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
}
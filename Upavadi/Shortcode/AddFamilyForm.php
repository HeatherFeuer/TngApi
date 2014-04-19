<?php

class Upavadi_Shortcode_AddFamilyForm extends Upavadi_Shortcode_AbstractShortcode
{
    const SHORTCODE = 'upavadi_pages_addfamilyform';

    //do shortcode Add Family form
    public function show()
    {
        ob_start();
        $personId = filter_input(INPUT_GET, 'personId', FILTER_SANITIZE_SPECIAL_CHARS);
        Upavadi_Pages::instance()->addfamilyForm($personId);
        return ob_get_clean();
    }
}
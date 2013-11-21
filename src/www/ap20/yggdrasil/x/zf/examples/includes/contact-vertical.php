<h2>A contact form</h2>

<?php

    // include the Zebra_Form class
    require '../Zebra_Form.php';

    // instantiate a Zebra_Form object
    $form = new Zebra_Form('form');

    // the label for the "name" element
    $form->add('label', 'label_name', 'name', 'Your name:');

    // add the "name" element
    // the "&" symbol is there so that $obj will be a reference to the object in PHP 4
    // for PHP 5+ there is no need for it
    $obj = & $form->add('text', 'name');

    // set rules
    $obj->set_rule(array(

        // error messages will be sent to a variable called "error", usable in custom templates
        'required' => array('error', 'Name is required!')

    ));

    // "email"
    $form->add('label', 'label_email', 'email', 'Your email address:');
    $obj = & $form->add('text', 'email');
    $obj->set_rule(array(
        'required'  =>  array('error', 'Email is required!'),
        'email'     =>  array('error', 'Email address seems to be invalid!'),
    ));

    // "subject"
    $form->add('label', 'label_subject', 'subject', 'Subject');
    $obj = & $form->add('text', 'subject', '', array('style' => 'width:400px'));
    $obj->set_rule(array(
        'required' => array('error', 'Subject is required!')
    ));

    // "message"
    $form->add('label', 'label_message', 'message', 'Message:');
    $obj = & $form->add('textarea', 'message');
    $obj->set_rule(array(
        'required' => array('error', 'Message is required!'),
        'length' => array(0, 140, 'error', 'Maximum length is 140 characters!', true)
    ));

    // "submit"
    $form->add('submit', 'btnsubmit', 'Submit');

    // if the form is valid
    if ($form->validate()) {

        // show results
        show_results();

    // otherwise
    } else

        // generate output using a custom template
        $form->render();

?>
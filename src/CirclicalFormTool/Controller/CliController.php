<?php

/**
,,
`""*3b..											
     ""*3o.					  						7/6/15 3:57 PM
         "33o.			                  			S. Alexandre M. Lemaire
           "*33o.                                 	
              "333o.								Redistribution of these files is illegal.
                "3333bo...       ..o:
                  "33333333booocS333    ..    ,.
               ".    "*3333SP     V3o..o33. .333b
                "33o. .33333o. ...A33333333333333b
          ""bo.   "*33333333333333333333P*33333333:
             "33.    V333333333P"**""*"'   VP  * "l
               "333o.433333333X
                "*3333333333333AoA3o..oooooo..           .b
                       .X33333333333P""     ""*oo,,     ,3P
                      33P""V3333333:    .        ""*****"
                    .*"    A33333333o.4;      .
                         .oP""   "333333b.  .3;
                                  A3333333333P
                                  "  "33333P"
                                      33P*"
		                              .3"
                                     "
                                     
                                     
*/

namespace CirclicalFormTool\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Exception\RuntimeException;

class CliController extends AbstractActionController
{

	private $module;
	private $form;
	private $doctrine;
	private $class;




	const C_FAILURE = "\033[41m";
	const C_SUCCESS = "\033[1;32m";
	const C_RESET   = "\033[0m";
	const C_CYAN    = "\033[0;36m";


	public function createFormAction()
	{
		$request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

		$this->module   = ucfirst( $this->params()->fromRoute( 'module' ) );
		$this->form     = ucfirst( $this->params()->fromRoute( 'form' ) );
		$this->doctrine = $this->params()->fromRoute( 'doctrine' );
		$this->class    = ucfirst( $this->params()->fromRoute( 'class' ) );

		if( empty( $this->module ) )
			return self::C_FAILURE . "You must specify the module in which the form is configured." . self::C_RESET . "\n";

		if( empty( $this->form ) )
			return self::C_FAILURE . "Please give your form a name\n";

		if( stristr( $this->form, 'Form' ) !== false )
			return self::C_FAILURE . "Don't include the word 'Form' in your form name, I'll take care of that." . self::C_RESET . "\n";

		if( !file_exists( getcwd() . '/module/' . $this->module ) )
			return self::C_FAILURE . "The module {$this->module} doesn't exist!" . self::C_RESET . "\n";

		$files_created = [
			'Form/' . $this->form . 'Form.php',
		    'Factory/Form/' . $this->form . 'FormFactory.php',
		    'InputFilter' . $this->form . 'InputFilter.php',
		    'Factory/InputFilter/' . $this->form . 'InputFilterFactory.php',
		];

		$base = getcwd() . "/module/{$this->module}/src/{$this->module}/";
		foreach( $files_created as $f )
			if( file_exists( $base . $f ) )
				return self::C_FAILURE . "Sorry! $f already exists in module {$this->module}!" . self::C_RESET . "\n";


		$this->generateForm();
		$this->generateFormFactory();
		$this->generateInputFilter();
		$this->generateInputFilterFactory();

		$str  = self::C_SUCCESS . "Success! Add these lines to your config:" . self::C_RESET  . "\n";
		$str .= self::C_CYAN . "1. form_elements / factories:" . self::C_RESET . "\n";
		$str .= $this->form . 'Form::class => ' . $this->form . "FormFactory::class,\n\n";
		$str .= self::C_CYAN . "2. input_filters / factories:" . self::C_RESET . "\n";
		$str .= $this->form . 'InputFilter::class => ' . $this->form . "InputFilterFactory::class,\n\n";
		$str .= self::C_CYAN . "3. use statements:" . self::C_RESET . "\n";
		$str .= "use {$this->module}\\Form\\{$this->form}Form;\n";
		$str .= "use {$this->module}\\Factory\\Form\\{$this->form}FormFactory;\n";
		$str .= "use {$this->module}\\InputFilter\\{$this->form}InputFilter;\n";
		$str .= "use {$this->module}\\Factory\\InputFilter\\{$this->form}InputFilterFactory;\n";

		return $str;

	}

	private function generateForm()
	{
		$dir      = getcwd() . "/module/{$this->module}/src/{$this->module}/Form";
		$template = file_get_contents( __DIR__ . '/../Resources/Form.txt' );
		$template = str_replace( [ 'MODULE', 'FORM' ], [ $this->module, $this->form ], $template );

		mkdir( $dir, 0755, true );
		file_put_contents( $dir . "/{$this->form}Form.php", $template );
	}

	private function generateFormFactory()
	{
		$dir      = getcwd() . "/module/{$this->module}/src/{$this->module}/Factory/Form";
		$template = file_get_contents( __DIR__ . '/../Resources/' . ($this->doctrine ? 'Doctrine' : '')  . 'FormFactory.txt' );
		$template = str_replace(
			[
				'HYDRATORFORM',
				'DHYDRATORUSE',
			    'HYDRATORUSER',
			    'MODULE',
				'FORM',
			],
			[
			    $this->class ? '$form->setObject( new ' . $this->class . '() );' : '',
			    $this->class ? 'use ' . $this->module . '\\Entity\\' . $this->class . ';' : '',
			    $this->class ? 'use ' . $this->module . '\\Model\\' . $this->class . ';' : '',
			    $this->module,
			    $this->form,

			],
			$template
		);

		mkdir( $dir, 0755, true );
		file_put_contents( $dir . "/{$this->form}FormFactory.php", $template );
	}

	private function generateInputFilter()
	{
		$dir      = getcwd() . "/module/{$this->module}/src/{$this->module}/InputFilter";
		$template = file_get_contents( __DIR__ . '/../Resources/InputFilter.txt' );
		$template = str_replace( [ 'MODULE', 'FORM' ], [ $this->module, $this->form ], $template );

		mkdir( $dir, 0755, true );
		file_put_contents( $dir . "/{$this->form}InputFilter.php", $template );
	}


	private function generateInputFilterFactory()
	{
		$dir      = getcwd() . "/module/{$this->module}/src/{$this->module}/Factory/InputFilter";
		$template = file_get_contents( __DIR__ . '/../Resources/InputFilterFactory.txt' );
		$template = str_replace( [ 'MODULE', 'FORM' ], [ $this->module, $this->form ], $template );

		mkdir( $dir, 0755, true );
		file_put_contents( $dir . "/{$this->form}InputFilterFactory.php", $template );
	}



}
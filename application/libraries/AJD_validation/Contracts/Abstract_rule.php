<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Rule_interface;
use AJD_validation\AJD_validation;
use AJD_validation\Helpers\Errors;

abstract class Abstract_rule extends AJD_validation implements Rule_interface
{
    protected $name;

	public function __invoke($value, $satisfier = NULL, $field = NULL)
    {
        return $this->run($value, $satisfier, $field);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

 	public function getExceptionError($value, array $extraParams = array(), $rule = NULL, $overrideName = FALSE)
    {
    	$currentClass	= $this;
    	$currentObj 	= $this;

    	if( !EMPTY( $rule ) )
    	{
    		$currentClass 	= $rule;
    		$currentObj 	= $rule;
    	}

        $exception          = $this->createException($rule);
        $name               = $this->name ?: Errors::stringify($value);

        $params             = array_merge(
            get_class_vars( get_class($currentClass) ),
            get_object_vars($currentObj),
            $extraParams,
            compact('value')
        );

        if( $overrideName )
        {
            $params['field']    = $name;
        }

        $exception->configure($params);

        $exception->setName($name);

        return $exception;
    }

    public function assertErr( $value, $override = FALSE )
    {
        if( $this->validate( $value ) )
        {
            return TRUE;
        }

        throw $this->getExceptionError( $value, array(), NULL, $override );
    }

 	protected function createException($rule = NULL)
    {
        $err        = static::get_errors_instance();
        $ruleStr    = NULL;

    	if( !EMPTY( $rule ) )
    	{
    		$currentRule	= get_class( $rule );
            $ruleStr        = get_class( $rule );
    	}
    	else
    	{
     		$currentRule 	= get_called_class();
            $ruleStr        = get_called_class();
     	}

        $currentRule        = str_replace('\\Rules\\', '\\Exceptions\\', $currentRule);
        $currentRule        .= '_exception';
        
        if( !EMPTY( Errors::getExceptionDirectory() ) )
        {
            foreach( Errors::getExceptionDirectory() as $key => $directory )
            {
                $namespace      = '';
                $addExceptionNamespace  = Errors::getExceptionNamespace();

                if( ISSET( $addExceptionNamespace[ $key ] ) )
                {
                    $namespace  = $addExceptionNamespace[ $key ];
                }

                $exceptionPath  = $directory.$ruleStr.'_exception.php';

                $requiredFiles  = get_required_files();

                $search         = array_search($exceptionPath, $requiredFiles);

                if( file_exists($exceptionPath) AND EMPTY( $search ) )
                {
                    $currentRule = $namespace.$ruleStr.'_exception';

                    $check  = require $exceptionPath;
                }
            }
        }
        
        return new $currentRule();
    }

    public function getCLientSideFormat( $field, $rule, $jsTypeFormat, $clientMessageOnly = FALSE, $satisfier = NULL, $error = NULL, $value = NULL )
    {
        return array();
    }

    protected function processJsArr( array $js, $field, $rule, $clientMessageOnly = FALSE )
    {
        $newJsFormat            = '';
        $newJsArr               = array(
            'customJS'          => ''
        );

        if( $clientMessageOnly )
        {
            if( ISSET( $js[$field][$rule][$clientMessageOnly] ) )
            {
                $newJsFormat    = $js[$field][$rule][$clientMessageOnly];
            }
            else
            {
                $newJsFormat    = $js[$field][$rule]['message'];
            }
        }
        else
        {
            if( ISSET($js[$field][$rule]['js']) )
            {
                $newJsArr['customJS']   .= $js[$field][$rule]['js'];
                unset($js[$field][$rule]['js']);
            }

            $newJsFormat        = implode(' ', $js[$field][$rule]);
        }

        $newJsArr[$field][$rule]    = $newJsFormat;

        return $newJsArr;
    }
}
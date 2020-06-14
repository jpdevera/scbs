<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_all;
use AJD_validation\Vefja\Vefja;

class All_rule extends Abstract_all
{
	public function run( $value, $satisfier = NULL, $field = NULL )
	{
		if( !EMPTY( $this->getRules() ) )
		{
			foreach( $this->getRules() as $rule )
			{
				if( !$rule->run( $value, NULL, $field ) )
				{
					return FALSE;
				}
			}
		}

		return TRUE;
	}

	public function validate( $value )
	{
		if( !EMPTY( $this->getRules() ) )
		{
			foreach( $this->getRules() as $rule )
			{
				if( !$rule->validate( $value ) )
				{
					return FALSE;
				}
			}
		}

		return TRUE;
	}

	public function assertErr( $value, $override = FALSE )
	{
		$exceptions 	= $this->assertRules( $value, $override );
		$numRules 		= count( $this->rules );
		$numExceptions 	= count( $exceptions );
		$summary 		= array(
			'total' 	=> $numRules,
			'failed'	=> $numExceptions,
			'passed'	=> $numRules - $numExceptions
		);

		if( !EMPTY( $exceptions ) )
		{
			throw $this->getExceptionError( $value, $summary, NULL, $override )->setRelated( $exceptions );
		}

		return TRUE;
	}
}
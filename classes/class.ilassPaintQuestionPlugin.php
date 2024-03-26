<?php
	include_once "./Modules/TestQuestionPool/classes/class.ilQuestionsPlugin.php";
	
	/**
	* assPaintQuestion plugin
	*
	* @author Yves Annanias <yves.annanias@llz.uni-halle.de>
	* @version $Id$
	* * @ingroup ModulesTestQuestionPool
	*
	*/
	class ilassPaintQuestionPlugin extends ilQuestionsPlugin
	{
	    final function getPluginName() : string
		{
			return "assPaintQuestion";
		}
		
		final function getQuestionType()
		{
			return "assPaintQuestion";
		}
		
		final function getQuestionTypeTranslation() : string
		{
			return $this->txt('questionType');
		}
	}
?>

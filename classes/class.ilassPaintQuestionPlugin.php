<?php
	/**
	* assPaintQuestion plugin
	*
	* @author Yves Annanias <yves.annanias@llz.uni-halle.de>
	* @author Christoph Jobst <cjobst@wifa.uni-leipzig.de>
	* @version $Id$
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
		
		public function uninstall() : bool
		{
		    if (parent::uninstall()) {
		        $this->db->dropTable('il_qpl_qst_paint_check', false);
		        $this->db->dropTable('il_qpl_qst_paint_image', false);
		        $this->db->dropTable('il_qpl_qst_paint_conf', false);
		    }
		    return true;
		}
	}
?>

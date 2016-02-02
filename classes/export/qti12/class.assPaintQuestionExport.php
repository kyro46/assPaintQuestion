<?php

include_once "./Modules/TestQuestionPool/classes/export/qti12/class.assQuestionExport.php";

/**
* Paint question export
*
* @author	Fred Neumann <fred.neumann@fau.de>
* @version	$Id:  $
* @ingroup ModulesTestQuestionPool
*/
class assPaintQuestionExport extends assQuestionExport
{
	/**
	* Returns a QTI xml representation of the question
	*
	* @return string The QTI xml representation of the question
	* @access public
	*/
	function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false)
	{
		global $ilias;
		
		include_once("./Services/Xml/classes/class.ilXmlWriter.php");
		$a_xml_writer = new ilXmlWriter;
		// set xml header
		$a_xml_writer->xmlHeader();
		$a_xml_writer->xmlStartTag("questestinterop");
		$attrs = array(
			"ident" => "il_".IL_INST_ID."_qst_".$this->object->getId(),
			"title" => $this->object->getTitle(),
			"maxattempts" => $this->object->getNrOfTries()
		);
		$a_xml_writer->xmlStartTag("item", $attrs);
		// add question description
		$a_xml_writer->xmlElement("qticomment", NULL, $this->object->getComment());
		// add estimated working time
		$workingtime = $this->object->getEstimatedWorkingTime();
		$duration = sprintf("P0Y0M0DT%dH%dM%dS", $workingtime["h"], $workingtime["m"], $workingtime["s"]);
		$a_xml_writer->xmlElement("duration", NULL, $duration);
		// add ILIAS specific metadata
		$a_xml_writer->xmlStartTag("itemmetadata");
		$a_xml_writer->xmlStartTag("qtimetadata");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "ILIAS_VERSION");
		$a_xml_writer->xmlElement("fieldentry", NULL, $ilias->getSetting("ilias_version"));
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "QUESTIONTYPE");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getQuestionType());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "AUTHOR");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getAuthor());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "POINTS");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getPoints());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		//Question specific
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "allowDifferentLineSize");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getLineValue());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		//
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "allowDifferentColors");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getColorValue());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		
		// backgroundImage
		if 	($this->object->getImageFilename() != "")
		{
			$imagetype = "image/jpeg";
			if (preg_match("/.*\.(png|gif)$/", $this->object->getImageFilename(), $matches))
			{
				$imagetype = "image/" . $matches[1];
			}
			$a_xml_writer->xmlStartTag("qtimetadatafield");
			$a_xml_writer->xmlElement("fieldlabel", NULL, "imagelabel");
			$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getImageFilename());
			$a_xml_writer->xmlEndTag("qtimetadatafield");
			$a_xml_writer->xmlStartTag("qtimetadatafield");
			$a_xml_writer->xmlElement("fieldlabel", NULL, "imagetype");
			$a_xml_writer->xmlElement("fieldentry", NULL, $imagetype);
			$a_xml_writer->xmlEndTag("qtimetadatafield");
				
			$imagepath = $this->object->getImagePath() . $this->object->getImageFilename();
			$fh = fopen($imagepath, "rb");
			if ($fh == false)
			{
				global $ilErr;
				$ilErr->raiseError($this->object->lng->txt("error_open_image_file"), $ilErr->MESSAGE);
				return;
			}
			$imagefile = fread($fh, filesize($imagepath));
			fclose($fh);
			$base64 = base64_encode($imagefile);
			$a_xml_writer->xmlStartTag("qtimetadatafield");
			$a_xml_writer->xmlElement("fieldlabel", NULL, "backgroundimage");
			$a_xml_writer->xmlElement("fieldentry", NULL, $base64);
			$a_xml_writer->xmlEndTag("qtimetadatafield");
		}
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "radiooption");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getRadioOption());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "canvasheight");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getCanvasHeight());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "canvaswidth");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getCanvasWidth());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		// End Question specific

		// additional content editing information
		$this->addAdditionalContentEditingModeInformation($a_xml_writer);
		$this->addGeneralMetadata($a_xml_writer);

		$a_xml_writer->xmlEndTag("qtimetadata");
		$a_xml_writer->xmlEndTag("itemmetadata");

		// PART I: qti presentation
		$attrs = array(
			"label" => $this->object->getTitle()
		);
		$a_xml_writer->xmlStartTag("presentation", $attrs);
		// add flow to presentation
		$a_xml_writer->xmlStartTag("flow");
		// add material with question text to presentation
		$this->object->addQTIMaterial($a_xml_writer, $this->object->getQuestion());

		$a_xml_writer->xmlEndTag("flow");
		$a_xml_writer->xmlEndTag("presentation");


		// PART III: qti itemfeedback
		$feedback_allcorrect = $this->object->feedbackOBJ->getGenericFeedbackExportPresentation(
			$this->object->getId(), true
		);

		$feedback_onenotcorrect = $this->object->feedbackOBJ->getGenericFeedbackExportPresentation(
			$this->object->getId(), false
		);

		$attrs = array(
			"ident" => "Correct",
			"view" => "All"
		);
		$a_xml_writer->xmlStartTag("itemfeedback", $attrs);
		// qti flow_mat
		$a_xml_writer->xmlStartTag("flow_mat");
		$a_xml_writer->xmlStartTag("material");
		$a_xml_writer->xmlElement("mattext");
		$a_xml_writer->xmlEndTag("material");
		$a_xml_writer->xmlEndTag("flow_mat");
		$a_xml_writer->xmlEndTag("itemfeedback");
		if (strlen($feedback_allcorrect))
		{
			$attrs = array(
				"ident" => "response_allcorrect",
				"view" => "All"
			);
			$a_xml_writer->xmlStartTag("itemfeedback", $attrs);
			// qti flow_mat
			$a_xml_writer->xmlStartTag("flow_mat");
			$this->object->addQTIMaterial($a_xml_writer, $feedback_allcorrect);
			$a_xml_writer->xmlEndTag("flow_mat");
			$a_xml_writer->xmlEndTag("itemfeedback");
		}
		if (strlen($feedback_onenotcorrect))
		{
			$attrs = array(
				"ident" => "response_onenotcorrect",
				"view" => "All"
			);
			$a_xml_writer->xmlStartTag("itemfeedback", $attrs);
			// qti flow_mat
			$a_xml_writer->xmlStartTag("flow_mat");
			$this->object->addQTIMaterial($a_xml_writer, $feedback_onenotcorrect);
			$a_xml_writer->xmlEndTag("flow_mat");
			$a_xml_writer->xmlEndTag("itemfeedback");
		}

		$a_xml_writer->xmlEndTag("item");
		$a_xml_writer->xmlEndTag("questestinterop");

		$xml = $a_xml_writer->xmlDumpMem(FALSE);
		if (!$a_include_header)
		{
			$pos = strpos($xml, "?>");
			$xml = substr($xml, $pos + 2);
		}
		return $xml;
	}
}
?>
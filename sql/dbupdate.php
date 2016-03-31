<#1>
<?php
	// Trage PaintQuestion als neuen Fragetyp ein, wenn es diesen noch nicht gibt
	$res = $ilDB->queryF("SELECT * FROM qpl_qst_type WHERE type_tag = %s",
		array('text'),
		array('assPaintQuestion')
	);
	if ($res->numRows() == 0)
	{
		$res = $ilDB->query("SELECT MAX(question_type_id) maxid FROM qpl_qst_type");
		$data = $ilDB->fetchAssoc($res);
		$max = $data["maxid"] + 1;

		$affectedRows = $ilDB->manipulateF("INSERT INTO qpl_qst_type (question_type_id, type_tag, plugin) VALUES (%s, %s, %s)", 
			array("integer", "text", "integer"),
			array($max, 'assPaintQuestion', 1)
		);
	}
?>
<#2>
<?php
	// speichere angegebenes hintergrundbild
	$fields = array(
			'question_fi'	=> array('type' => 'integer', 'length' => 4, 'notnull' => true ),
			'image_file' 	=> array('type' => 'text', 'length' => 200, 'fixed' => false, 'notnull' => true )
	);
	$ilDB->createTable("il_qpl_qst_paint_image", $fields);
	$ilDB->addPrimaryKey("il_qpl_qst_paint_image", array("question_fi"));	
	
	// erlaube farbauswahl und linienstärke? zeichengoesse gem. image oder selbst festlegen?
	$fields = array(
			'question_fi'	=> array('type' => 'integer', 'length' => 4, 'notnull' => true ),
			'line' 			=> array('type' => 'integer', 'length' => 1),
			'color' 		=> array('type' => 'integer', 'length' => 1),
			'radio_option' 	=> array('type' => 'text', 'length' => 16, 'notnull' => true, 'fixed' => false, 'default' => 'radioImageSize'),		
			'width' 		=> array('type' => 'integer', 'length' => 8, 'default' => 100 ),
			'height' 		=> array('type' => 'integer', 'length' => 8, 'default' => 100 )
	);
	$ilDB->createTable("il_qpl_qst_paint_check", $fields);
	$ilDB->addPrimaryKey("il_qpl_qst_paint_check", array("question_fi"));				
?>
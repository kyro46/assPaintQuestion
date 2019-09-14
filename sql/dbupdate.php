<#1>
<?php
	//Add Paint Question Type
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
	//Save Backgroundimage
	$fields = array(
			'question_fi'	=> array('type' => 'integer', 'length' => 4, 'notnull' => true ),
			'image_file' 	=> array('type' => 'text', 'length' => 200, 'fixed' => false, 'notnull' => true )
	);
	$ilDB->createTable("il_qpl_qst_paint_image", $fields);
	$ilDB->addPrimaryKey("il_qpl_qst_paint_image", array("question_fi"));	
	
	//Enable Colourselection, Brushsize, Canvassize definition
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
<#3>
<?php
	//Add information if scaled image exists - needed for backward compatibility
    if(!$ilDB->tableColumnExists('il_qpl_qst_paint_check', 'resized'))
    {
        $ilDB->addTableColumn('il_qpl_qst_paint_check', 'resized', array(
                'type' => 'integer',
        		'length' => '1',
        		'default' => 0,
                'notnull' => false,
            )
        );
    }
?>
<#4>
<?php
	//Create missing scaled images on update
	$web_path = ilUtil::getWebspaceDir();
	$assessment_path = $web_path."/assessment/";

	//list of paint questions with resized images: question_id, obj_fi, image_file, width, height
	$affected_paint_questions_query = $ilDB->query("select C.question_id, C.obj_fi, B.image_file, A.width, A.height from il_qpl_qst_paint_check A inner join il_qpl_qst_paint_image B inner join qpl_questions C on A.question_fi = B.question_fi AND A.question_fi = C.question_id WHERE A.radio_option = 'radioOwnSize' AND A.resized = 0");
	
	while ($resize_paint_question = $ilDB->fetchAssoc($affected_paint_questions_query)) {
		$path = $assessment_path . $resize_paint_question['obj_fi'] . "/" . $resize_paint_question['question_id'] . "/images/";
		$destination = $path .'resized_'. $resize_paint_question['image_file'];
		
		list ( $width, $height, $type ) = getimagesize ( $path . $resize_paint_question['image_file']);
		
		switch ( $type )
		{
			case 1:
				$image = imagecreatefromgif ($path . $resize_paint_question['image_file']);
				break;
			case 2:
				$image= imagecreatefromjpeg ($path . $resize_paint_question['image_file']);
				break;
			case 3:
				$image= imagecreatefrompng ($path . $resize_paint_question['image_file']);
		}
		
		// Neue Größe berechnen
		$newwidth =  $resize_paint_question['width'];
		$newheight = $resize_paint_question['height'];
		
		// Bild laden
		$resized= imagecreatetruecolor($newwidth, $newheight);
		
		// Skalieren
		imagecopyresized($resized, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		
		//Speichern
		switch ( $type )
		{
			case 1:
				imagegif($resized, $destination);
				break;
			case 2:
				imagejpeg($resized, $destination, 100);
				break;
			case 3:
				imagepng ($resized, $destination, 9);
		}
		
		$ilDB->manipulate('update il_qpl_qst_paint_check set '.'resized = ' . 1 .' '.'WHERE question_fi = '. $ilDB->quote($resize_paint_question['question_id']));
	}
?>
<#5>
<?php
	//Add option to configure the amount of saved images during backup
    if(!$ilDB->tableColumnExists('il_qpl_qst_paint_check', 'log_count'))
    {
        $ilDB->addTableColumn('il_qpl_qst_paint_check', 'log_count', array(
                'type' => 'integer',
        		'length' => '3',
        		'default' => 3,
                'notnull' => false,
            )
        );
    }
?>
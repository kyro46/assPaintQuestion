# assPaintQuestion
Paint-Questiontypeplugin for ILIAS 5.2.x

For ILIAS 4.3, 4.4, 5.0 and 5.1 see the [**Releases**](https://github.com/kyro46/assPaintQuestion/releases)

### Questiontype that allows drawing on a backgroundimage or a plain canvas ###

This plugin will add a questiontype, that allows:
* drawing on a canvas
* adding a backgroundimage and drawing on it
* changing the color
* changing the pensize
* undo/redo/delete painted lines

### Installation ###

* Customizing/global/plugins/Modules/TestQuestionPool/Questions
```bash
mkdir -p Customizing/global/plugins/Modules/TestQuestionPool/Questions  
cd Customizing/global/plugins/Modules/TestQuestionPool/Questions
git clone https://github.com/kyro46/assPaintQuestion.git
```  
and activate it in the ILIAS-Admin-GUI. Manual correction has to be enabled for this question type.

### Known Problems ###

* gd-jpeg, libjpeg: recoverable error - Please check "gd.jpeg_ignore_warning" in your php.ini, see [PHP Docs](https://secure.php.net/manual/en/image.configuration.php)

### Credits ###
* Development of plugin-draft for ILIAS 4.4 by Yves Annanias, University Halle, 2014
* Further development by Christoph Jobst, University Halle/Leipzig, 2014/2015/2016/2017
* The plugin (1.1.10+) utilises [literallycanvas](https://github.com/literallycanvas/literallycanvas) by Steve Johnson
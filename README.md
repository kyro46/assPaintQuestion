# assPaintQuestion
Paint-Questiontypeplugin for ILIAS 4.3.x

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

### Credits ###
* Development of plugin-draft for ILIAS 4.3 by Yves Annanias, University Halle, 2014
* Further development by Christoph Jobst, University Halle, 2014/2015/2016
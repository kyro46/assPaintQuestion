# assPaintQuestion
Paint-Questiontypeplugin for ILIAS 9

For ILIAS 4.3 to 8 see the [**Releases**](https://github.com/kyro46/assPaintQuestion/releases) and the corresponding branches.


### Questiontype that allows drawing on a backgroundimage or a plain canvas ###

This plugin will add a questiontype, that allows:
* drawing on a canvas
* adding a backgroundimage and drawing on it
* changing the color
* changing the pensize
* undo/redo/delete painted lines
* keep an configurable amount of historized images for logging purposes (files accessible by the server admin)

### Installation ###

Install the plugin

```bash
mkdir -p Customizing/global/plugins/Modules/TestQuestionPool/Questions  
cd Customizing/global/plugins/Modules/TestQuestionPool/Questions
git clone https://github.com/kyro46/assPaintQuestion.git
```

Then follow the usual steps to update ILIAS from the ILIAS-Root-Dir:

```bash
composer install --no-dev
php setup/setup.php update
```

and activate it in the ILIAS-Admin-GUI. Manual correction has to be enabled for this question type.

### Known Problems ###

* gd-jpeg, libjpeg: recoverable error - Please check "gd.jpeg_ignore_warning" in your php.ini, see [PHP Docs](https://secure.php.net/manual/en/image.configuration.php)

### Credits ###
* Development of plugin-draft for ILIAS 4.4 by Yves Annanias, University Halle, 2014
* Further development by Christoph Jobst, University Halle/Leipzig, 2014+
* The plugin (since 1.1.10+) utilises [literallycanvas](https://github.com/literallycanvas/literallycanvas) (BSD-2-Clause) by Steve Johnson
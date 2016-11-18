Place your plugin language files in this directory.
$> svn export http://i18n.svn.wordpress.org/tools/trunk/
$> php makepot.php wp-plugin /var/www/html/website.com/wp-content/plugins/die-klinke die-klinke.pot
$> msgfmt -o die-klinke-de_DE.mo die-klinke-de_DE.po


Please visit the following links to learn more about translating WordPress plugins:
https://codex.wordpress.org/I18n_for_WordPress_Developers
https://programming-review.com/generating-pot-files-for-themes-and-plugins/

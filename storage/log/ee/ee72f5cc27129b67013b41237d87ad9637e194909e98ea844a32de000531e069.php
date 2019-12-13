<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* file.php */
class __TwigTemplate_d90679ef4e71ae1646b373adae6d1e308f4e12ed13fe84bd84937804a3901244 extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<!DOCTYPE html>
<html lang=\"en\">

<?php require_once(APPPATH.'/views/site/partials/head.php'); ?>

<body data-spy=\"scroll\" data-target=\"#navigation\" data-offset=\"50\">
    <div id=\"app\" v-cloak>

        <?php require_once(APPPATH.'/views/site/partials/nav.php'); ?>

        <div class=\"container\">
            <section id=\"features-wrap\" name=\"features-wrap\">
                <div id=\"features\">
                    <div class=\"container\">
                        <div class=\"row \">
                            <div class=\"col-md-8 col-xs-12 col-md-offset-2\">
                                <div class=\"panel panel-default\">
                                    <div class=\"panel-heading text-center\">
                                        <h3 class=\"panel-title\">File Information</h3>
                                    </div>
                                    <div class=\"panel-body no-padding\">

                                    <?php if (\$File->get(\"file_thumbnail\")): ?>
                                        <div class=\"text-center\" style=\"padding:2em;\">
                                            <img width=\"200\" src=\"<?= \$File->get(\"file_thumbnail\") ?>\" alt=\"<?= \$File->get(\"file_name\") ?>\">
                                        </div>
                                    <?php endif; ?>

                                        <table class=\"table\">
                                            <tbody>
                                                <tr>
                                                    <td style=\"width: 100px;\">File Name</td>
                                                    <td style=\"width: 10px;\">:</td>
                                                    <td><?= \$File->get(\"file_name\") ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Type</td>
                                                    <td>:</td>
                                                    <td><?= \$File->get(\"file_mimeType\") ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Size</td>
                                                    <td>:</td>
                                                    <td><?= readableFileSize(\$File->get(\"file_size\")) ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Added on</td>
                                                    <td>:</td>
                                                    <td style=\"color:#6495ed\"><?= \$File->get(\"created\") ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <center>

                                            <?php if (\$AuthUser): ?>
                                                <input type=\"hidden\" name=\"id\" id=\"id\" value=\"<?= \$fileId ?>\">
                                                <button type=\"button\"
                                                    class=\"btn btn-download btn-success btn-sm\">Download</button>
                                            <?php else: ?>
                                                <a href=\"<?= APPURL . \"/signin?redirect=\" . urlencode(currentUrl()) ?>\" class=\"btn btn-sm btn-danger\">Connect
                                                    Drive to download</a>
                                            <?php endif; ?>

                                        </center>
                                    </div>
                                    <div class=\"panel-footer text-center\">
                                        <br><h3 class=\"panel-title\">Share this file</h3>
                                        <div id=\"share\"></div>
                                        <div class=\"input-group\">

                                            <?php \$copyId = readableRandomString(12) ?>

                                            <input type=\"text\" id=\"<?= \$copyId ?>\" class=\"form-control\" value=\"<?= currentUrl() ?>\">
                                            <span class=\"input-group-btn\">
                                                <button type=\"button\" class=\"btn btn-copy btn-default btn-flat\" data-clipboard-target=\"#<?= \$copyId ?>\">
                                                    <i class=\"fa fa-clone\"></i>
                                                </button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>

        <?php require_once APPPATH . '/views/site/partials/footer.php';?>

    </div>

    <?php require_once APPPATH . '/views/site/partials/script.php';?>

    <script>
        \$(\"#share\").jsSocials({
            showCount: true,
            shares: [\"email\", \"twitter\", \"facebook\", \"whatsapp\"]
        });
        var docHeight = \$(window).height();
        var footerHeight = \$('#footer').height();
        var footerTop = \$('#footer').position().top + footerHeight;

        if (footerTop < docHeight)
            \$('#footer').css('margin-top', 10+ (docHeight - footerTop) + 'px');
    </script>

</body>

</html>";
    }

    public function getTemplateName()
    {
        return "file.php";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "file.php", "E:\\Laragon\\www\\gdsharer\\app\\views\\site\\file.php");
    }
}

<?php

namespace Stanford\RequestActivationOverride;

require_once("emLoggerTrait.php");

use ExternalModules\AbstractExternalModule;

class RequestActivationOverride extends AbstractExternalModule
{

    use emLoggerTrait;

    public function redcap_every_page_top()
    {
        $base = basename((PAGE_FULL));
        if ($base == "project.php") { // Project context
            try {
                //if superuser
                if (!SUPER_USER)
                    $this->override();
            } catch (\Exception $e) {
                $this->emError($e->getMessage());
            }
        }
    }


    /**
     * @throws \Exception
     */
    public function override()
    {
        $js_file_path = $this->getUrl('js/script.js');
        $survey_link = $this->getSurveyLink();

        print "<div id='redirect-uri' class='hidden' redirect-uri=$survey_link ></div>";
        print "<script type='module' src=$js_file_path></script>";
    }

    /**
     * Given a PID, returns the project name as a string
     * @returns String
     * @throws \Exception
     */
    public function getSurveyLink()
    {
        $settings = $this->getSystemSettings();
        $link = filter_var($settings["redcap-survey-redirect"]['value'], FILTER_SANITIZE_STRING);
        if (!$this->checkFormat($link))
            throw new \Exception('Incorrect Format of url applied in em config');
        else
            return $link;
    }

    /**
     *
     * @param $link
     * @throws \Exception
     */
    public function checkFormat($link)
    {
        if (isset($link)) {
            $check = [
                (strstr(parse_url($link, PHP_URL_HOST), 'localhost')),
                (strstr(parse_url($link, PHP_URL_HOST), 'redcap'))
            ];
            if (in_array(true, $check))
                return true;
            return false;

        } else {
            throw new \Exception('No config value passed for redcap-survey-redirect');
        }

    }
}


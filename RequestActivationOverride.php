<?php

namespace Stanford\RequestActivationOverride;

require_once("emLoggerTrait.php");

use ExternalModules\AbstractExternalModule;

class RequestActivationOverride extends AbstractExternalModule
{

    use emLoggerTrait;

    public function redcap_every_page_top()
    {
        $base = basename(PAGE_FULL);
        if ($base == "project.php") { // Project context
            try {
                if (SUPER_USER) //if superuser
                    $this->override();
            } catch (\Exception $e) {
                $this->emError($e->getMessage());
            }
        }
    }

    /**
     * Function that injects necessary info into the client before render
     * @throws \Exception
     */
    public function override()
    {
        $js_file_path = $this->getUrl('js/script.js'); //Override js file
        $survey_link = $this->getSurveyLink(); //URI to redirect to
        $finance_data = $this->fetchCostData();

        print "<div id='redirect-uri' class='hidden' redirect-uri=$survey_link ></div>";
        print "<script type='text/javascript'>var data1 = $finance_data;</script>";
        print "<script type='module' src=$js_file_path></script>";
    }

    /**
     * @return null | String
     * @throws \Exception
     */
    public function fetchCustomField()
    {
        $enable = $this->getSystemSetting('enable_custom_module_field');
        if ($enable) {
            $custom_field = $this->getSystemSetting('custom_module_field');

            if (!isset($custom_field))
                throw new \Exception('Error: no field set for setting enable_custom_module_field');
            else
                return $custom_field;
        } else { //Custom field has been disabled, skip
            return null;
        }
    }

    /**
     * Returns Array of records containing the cost information to be displayed on EM enable modal
     * @returns array
     * @throws \Exception
     */
    public function fetchCostData()
    {
        $finance_pid = $this->getSystemSetting('em-finance-pid');

        if (!isset($finance_pid))
            throw new \Exception('Error: no finance Project ID has been passed');

        $custom_field = $this->fetchCustomField();

        $fields = array(
            'module_name',
            'stanford_module',
            'module_description',
            'actual_monthly_cost',
            'maintenance_fee',
            $custom_field ?? ''
        );
        $params = array('project_id' => $finance_pid, 'return_format' => 'json', 'fields' => $fields, 'events' => 'modules_arm_1', 'filterLogic' => '[actual_monthly_cost] != 0');
        $result = json_decode(\REDCap::getData($params), true);

        if ($result && array_key_exists($custom_field, $result[0]))
            return $this->transformMap($result); //Fetch task record
        else
            throw new \Exception("No field by the name of $custom_field found");
    }

    /**
     * @param $data
     * @return String
     */
    public function transformMap($data)
    {
        $transform = array();
        foreach ($data as $entry) {
            $transform[$entry['module_name']] = $entry;
        }

        return json_encode($transform);
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
     * Checks format of link to ensure the url redirect is valid
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


# RequestActivationOverride
A System level EM that alters the 'Enable External Module' button to redirect to a survey URL.

This module is intended for regular users, and does not alter super user functionality.

Config options include:
- `redcap-survey-redirect`
  - URL of the corresponding survey to redirect to
- `em-finance-pid`
  - Project ID to pull finance information from (fields & instrument)
- `custom_module_field`
  - Field name of any additional text to be displayed to the user in EM enable modal.
  - This field must be present in the same project specified above.
- `enable_custom_module_field`
  - Enables the custom module field to be displayed as additional text under External Module Cost


Configuration example:
```text
redcap-survey-redirect = http://localhost/surveys/?s=WLHYC8CJEPAJ8KKF
em-finance-pid = 71
custom_module_field = custom_text_override
enable_custom_module_field = true
```

In this example, discoverable modals shown in the external module tab will have a request button that redirects to the survey specified by `redcap-survey-redirect`
In addition, the following finance information is pulled by default from project 71:
```injectablephp
$fields = array(
    'module_name',
    'stanford_module',
    'module_description',
    'actual_monthly_cost',
    'maintenance_fee',
    $custom_field ?? ''
);
```

`actual_monthly_cost` as well as `custom_field` will append new dynamic HTML to the modal body.

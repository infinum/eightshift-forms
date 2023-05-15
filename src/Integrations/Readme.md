# Integrations

Adding new integration steps:
1. Add new folder in the Integrations.
2. Add new class `Integrations/<Integration_Name>.php`. This holds mappings for API to our components.
3. Add new class `Integrations/<Integration_Name>Client.php`. This holds all data connections to the API.
4. Add new class `Integrations/Settings<Integration_Name>.php`. This holds all settings, global/local.
5. Populate new key with the filters in the `Hooks/Filters.php::ALL`.
6. Add new class `Rest/Routes/FormSubmit<Integration_Name>Route.php`.
7. Add new block to for the specific integration in the `Blocks/custom` folder.
8. Add new allowed block in the manifest.json of the `Blocks/custom/form-selector/manifest.json` block.
9. Add new API key global variable in the `Hooks/Variables.php`.
10. Add labels for new integration `Labels/Labels.php`.

# Goodbits

[API](https://gist.github.com/kalv/84c37780d277da5b7a3cdf5c28359c6b)

Version: **v1**

Type:
* form builder provided by the service.

Supported fields:
* Email
* First name
* Last name

# Greenhouse

[API](https://developers.greenhouse.io/job-board.html)

Version: **v1**

Type:
* form builder provided by the service.

Supported fields:
* Short textbox
* Long textbox
* Multi select
* Yes/No
* Attachment

# HubSpot

[API Submit](https://legacydocs.hubspot.com/docs/methods/forms/submit_form)
[API Files](https://legacydocs.hubspot.com/docs/methods/files/v3/upload_new_file)
[API Update Contact](https://legacydocs.hubspot.com/docs/methods/contacts/create_or_update)
[API Properties](https://legacydocs.hubspot.com/docs/methods/contacts/v2/get_contacts_properties)
[API Forms](https://legacydocs.hubspot.com/docs/methods/forms/v2/get_forms)

Version Submit: **v3**
Version Items: **v2**

Type:
* form builder provided by the service.

Supported fields:
* Text
* Number
* Textarea
* File
* Select
* Boolean Checkbox
* Checkbox
* Radio
* Consent
* Heading
* Paragraph

# Mailchimp

[API](https://mailchimp.com/developer/marketing/api/)

Version: **v3**

Type:
* form builder provided by the service.

Supported fields:
* Text
* Address - partially
* Number
* Phone
* Radio
* Dropdown

# Mailerlite

[API](https://developers.mailerlite.com/docs)

Version: **v2**

Type:
* form builder provided by the service.

Supported fields:
* Text
* Date
* Number

# Clearbit

[API](https://dashboard.clearbit.com/docs)

Version: **v2**

Type:
* connection made with Hubspot integration and the ability to map individual field.

Supported integrations:
* Hubspot

# ActiveCampaign

[API](https://developers.activecampaign.com/reference/overview)

Version: **v3**

Type:
* form builder provided by the service.

Supported fields:
* All

# Airtable

[API](https://airtable.com/developers/web/api/introduction)

Version: **v0 - beta**

Type:
* form builder provided by the service.

Supported fields:
* Input
* Email
* Url
* Number
* Phone number
* Textarea
* Single Select
* Multiple Choices
* Checkbox

# Jira

Type:
* form builder **not** provided by the service.
* the form is created using our forms fields and connected to Jira custom fields using form settings.

Supported fields:
* all

## Cloud solution

[API](https://developer.atlassian.com/cloud/jira/platform/rest/v3/intro/#version)
[Description nodes](https://developer.atlassian.com/cloud/jira/platform/apis/document/nodes/table_cell/)

Version: **v3**

## Self-hosted

[API](https://docs.atlassian.com/software/jira/docs/api/REST/9.8.0/#api/2)

Version: **v2**

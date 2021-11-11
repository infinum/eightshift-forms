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
9. Add transient cache to the cache busting list in the `Cache/SettingsCache.php::ALL_CACHE`.
10. Add new API key global variable in the `Hooks/Variables.php`.
11. Add labels for new integration `Labels/Labels.php`.

# Goodbits

[Documentation](https://gist.github.com/kalv/84c37780d277da5b7a3cdf5c28359c6b)

Version: **v1**

Supported fields:
* Email
* First name
* Last name

# Greenhouse

[Documentation](https://developers.greenhouse.io/job-board.html)

Version: **v1**

Supported fields:
* Short textbox
* Long textbox
* Multi select
* Yes/No
* Attachment

# HubSpot

[Documentation](https://legacydocs.hubspot.com/docs/methods/forms/submit_form)

Version Submit: **v3**
Version Items: **v2**

Supported fields:
* Text
* Textarea
* File
* Select
* Boolean Checkbox
* Checkbox
* Radio
* Consent

# Mailchimp

[Documentation](https://mailchimp.com/developer/marketing/api/)

Version: **v3**

Supported fields:
* Text
* Address - partially
* Number
* Phone
* Radio
* Dropdown

# Mailerlite

[Documentation](https://developers.mailerlite.com/docs)

Version: **v2**

Supported fields:
* Text
* Date
* Number

# Revisionize Workflow

The content workflow on CCS is currently maintained via Revisionize, a plugin for Wordpress that allows the creation of a draft for a particular page, with only specific authorised users being able to merge and publish those drafts to the frontend.

The plugin can be found at: `public/wp-content/plugins/revisionize`

By default Framework authors are unable to publish content to the live site. This process requires an editor or administrator to perform, so to allow the authors to draft content changes for publication, this plugin allows "Framework Authors" (an assigned Wordpress role) to go to edit a framework, and rather than saving or publishing directly, instead getting the option to "Revisionize". 

This creates a new "revision" draft of the content, which is separated from the original post, and can be freely edited without risk of publication. Once the draft is ready, it can be set to **"Pending Review"**, which informs the Editors/Administrators that it is ready to be checked and inspected for publishing to the live site.

Once the revision is published, its contents are merged into the original draft post from which it was created, and the post is published live.

## Modifications and Hooks

Some tweaks were made to the plugin's behaviour through the use of Wordpress hooks. In order to prevent confusion and multiple revisions of the same posts filling up the admin system, we removed the option to revisionize from a post if any of the following conditions are true:

* The post is archived;
* The post is pending review;
* The post is a revision;
* The post already has a revision (in which case, the revision already in existence should be edited instead)

These tweaks can be found in the ccs-custom plugin: `public/wp-content/plugins/ccs-custom/library/custom-revisionise.php`
 
More information on Revisionize can be found at their [official site](https://revisionize.pro/).
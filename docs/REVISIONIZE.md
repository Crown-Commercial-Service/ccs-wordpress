# Publish Press Revisions Workflow

PublishPress Revisions Workflow
The content workflow on CCS is now maintained via PublishPress Revisions, a WordPress plugin that allows the creation of a draft revision for a particular page, with only specific authorised users able to merge and publish those revisions to the frontend.

The plugin can be found at: public/wp-content/plugins/publishpress-revisions

By default Framework authors are unable to publish content to the live site. This process requires an editor or administrator to perform. PublishPress Revisions enables Framework Authors (an assigned WordPress role) to open a framework and create a revision instead of saving or publishing directly. This creates a new revision draft linked to the original post, which can be edited without risk to the live content. Once the revision is ready, it can be set to Pending Review, which informs the Editors/Administrators that it is ready to be checked and inspected for publication to the live site.

Once the revision is published, its contents are merged into the original post from which it was created, and the post is published live.

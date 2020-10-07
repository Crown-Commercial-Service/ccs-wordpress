# User Roles

User roles are created and administered in WordPress using the [Members WordPress plugin](https://wordpress.org/plugins/members/).

## Default User Roles

As of writing, the site includes the following default roles:

- Administrator
- Subscriber
- SEO Editor
- SEO Manager

The `Administrator` role is essentially equivalent to a "super user" role. Anyone assigned to the `Administrator` role will have permissions 
to access every area (and functionality) of the WordPress admin.

`Subscriber` is a default role that comes with WordPress by default and can be ignored.

The `SEO Editor` and `SEO Manager` roles are added by the [Yoast SEO plugin[(https://wordpress.org/plugins/wordpress-seo/). It is recommended that 
these roles aren't removed, as this may cause potential problems when updating the Yoast Plugin in the future. 

## Custom Roles

As of writing, the site includes the following custom roles:

- CCS Editor/ Administrator
- CCS Super Admin
- Framework Author
- Marketing Team Member

Please find a breakdown of these roles below:

### CCS Editor/ Administrator 

Has access to edit and publish content across the whole website. Although only has permissions to delete certain types of content.

Has access to add new users to the site.

### CCS Super Admin

Has access to edit, publish and delete content across the website. Has very limited restrictions when it comes to modifying content (lots cannot be deleted for example).

Has access to add new users to the site.

### Framework Author

This role is designed for users that should only be editing Frameworks (and lots, as they are assigned to Frameworks).

This role also has permissions to modify WordPress media, as frameworks can link to media items (e.g. PDF documents).

Please note that Framework authors don't actually have permissions to publish changes to Frameworks. This is desired, as all changes made to a Framework should 
be reviewed before being published.

This review process is facilitated by using the [Revisionize](https://wordpress.org/plugins/revisionize/) WordPress plugin. This essentially allows users to "revisionize" 
a Framework. Creating a copy of the published Framework which they can make modifications to. Once they've finished these modificaitons, they can request a user assigned the 
`CCS Editor/ Administrator` or `CCS Super Admin` to publish the Framework for them. Once the "Reivionized" Framework has been published, it overrideds the previously published 
Framework.

### Marketing Team Member

Only has permission to add and edit content to the following content types of the website:

- Pages
- Posts
- Events
- Webinars
- Whitepapers
- WordPress Media files

This user role is designed for users that need access to edit only "Marketing" areas of the site. So front-facing areas 
of the website that aren't Frameworks or Suppliers.

Please note that users assigned to this role aren't able to delete most content that they are able to edit. But they are able to unpublish content from the website if they want 
to temporarily (or permanently) remove it from public view. 

For long term deletion of content, it is preferable to get a user assigned to the `CCS Editor/ Administrator` or `CCS Super Admin` roles to delete it.

(Please also note that deleting content is permanent, database backups notwithstanding)


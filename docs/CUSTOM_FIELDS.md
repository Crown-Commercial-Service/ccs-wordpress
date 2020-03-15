# Custom Fields

To expand on Wordpress' core functionality with regards to defining custom fields for content, the website makes use of a couple of plugins.

## Advanced Custom Fields Pro

This plugin allows for the creation and customisation of a wide variety of field groups and allows content to be more easily customised in the CMS.

ACF Pro can be found at: `/public/wp-content/plugins/advanced-custom-fields-pro/`

Please note: we do not utilise the CMS functionality for ACF Pro. For all field customisation, we use Fewbricks.

## Fewbricks 

As an extension to ACF Pro, we have employed Fewbricks to allow us to manage all field customisation in code (and therefore version control).

Fewbricks allows us to define field groups, components, and utilise ACF to define when these fields should and should not appear.

Fewbricks can be found at: `/public/wp-content/plugins/fewbricks/`

### Field Customisation

All custom code for Fewbricks is defined within an additional plugin: **fewbricks_definitions**

This can be found at: `/public/wp-content/plugins/fewbricks_definitions/`

This is where all custom components and field groups for the the website are defined.

#### Components (fewbricks_definitions/bricks)

Components are reusable blocks of content with specific sets of fields, and rules for how they behave. Content such as a home page carousel, or a list of related files, might be defined as a single component.

Each component is a class that extends the Fewbricks `project_brick` class. In here you can add fields and rules to your liking. For example, if you wanted to add a "subtitle" to a component, you could use the following:

```		
        $this->add_field( new acf_fields\text( 'Subtitle', 'subtitle', '202003111614a', [
   			'instructions' => 'Enter some text here to act as your subtitle',
   		] ));
```

The add_field() argument accepts a standard ACF field object. Please be aware that the third argument for the field must be unique, so we have adopted the convention of using the current timestamp when creating the field to ensure this is the case.

More information can be found on the available fields and usage at the [ACF Pro docs](https://www.advancedcustomfields.com/resources/).

#### Field Groups (fewbricks_definitions/field-groups)

The field groups are where your actual content is configured. You can assign fields, field groups and bricks to specific post types, taxonomies and templates. 

As an example, take the landing template, which tend to have a hero banner along the top. The field or this can be added to pages that use the landing template by:

```
$location = [
	[
		[
			'param' => 'page_template',
			'operator' => '==',
			'value' => 'page-templates/landing.php'
		]
	]
];

$field_group = ( new fewacf\field_group( 'Hero', '202001081046a', $location, 10, [
	'position' => 'acf_after_title',
	'names_of_items_to_hide_on_screen' => [
		'excerpt',
        'the_content',
		'comments'
	]
]));


$field_group->add_brick((new bricks\component_hero('hero', '202001081046b')) );

$field_group->register();
```

The field in question references the component_hero, which has its own fields and is defined within the components area.

More information on Fewbricks can be found at their [official repo](https://github.com/folbert/fewbricks).

# iqnection-silverstripe-pages-pagebuilder
SilverStripe Minisite Page

# CMS Page Builder
Provides an interface to build out a custom content page.
Includes Content Builder Section
[Previews are still in development]


## Custom Modules
Custom modules can be created by extending class IQnection\PageBuilder\Section\PageBuilderSection
Sections might include forms, or predefined layouts, whatever you can build


## Content Builder Section
This section allows you to stack modules for unique content layouts. 
Each block is a row within the section
Includes blocks for:
- Headline
- Full width content
- Two column content
- RSS Blog feed (developed for WordPress feed)


### Custom Content Builder Blocks
Extend IQnection\PageBuilder\ContentBuilder\Block\Block to build your own


## Config
### Section Background Colors
Sections can be set to have background colors or images.
To set background colors, you must first declare the class in your stylesheets
Then add a config value for each class declared
Each value should be a key value, where the key is the CSS hex or rgba, and the value should be the class that will be assigned to the block
```
IQnection\PageBuilder\Section\PageBuilderSection:
  background_colors:
    '#000000': 'bg-black'
    '#ffffff': 'bg-white'
```

You can also assign borders to the top and bottom
This CSS is generated for you, so all you need to do is provide the colors to select from
```
IQnection\PageBuilder\Section\PageBuilderSection:
  border_colors:
    - '#000000'
    - '#ffffff'
```

### Headline Block
Set key value pairs for the color and class to assign the font color
Declare a class in your stylesheet that sets the font color
```
.text-black,
.text-black * { color:#000000 !important; }
```

Then add the value to your config to make it a selection
```
IQnection\PageBuilder\ContentBuilder\Block\Headline:
  colors:
    '#000000': 'text-black'
```

### Blog Feed Block
Set the cache lifetime for the feed
Use the format expected for php `strtotime()`
defaults to 1 hour
```
IQnection\PageBuilder\ContentBuilder\Block\BlogFeed:
  feed_cache_lifetime: '-1 hour'
```

## Hooks
IQnection\PageBuilder\PageBuilder:
- updateCustomCSS: add or edit the custom CSS thats generated from all sections

IQnection\PageBuilder\Section:
- updateCSSClasses: add or change CSS classes that are set on the section element
- updateCustomCSS: add or edit the custom CSS thats generated for the section element

IQnection\PageBuilder\ContentBuilder\Block\Block:
- updateSectionCSSClasses: add or change CSS classes that are assigned to the block element container
- updateCSSClasses: add or change CSS classes that are assigned to the block element
- updateCustomCSS: add or edit the custom CSS thats generated for the block element

## Import/Export
Still in development

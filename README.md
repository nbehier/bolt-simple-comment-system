!!! Work in progress

Simple comment system - Bolt Extension
======================================

[Bolt](https://bolt.cm/) extension to add a simple local comment system

### Known limitations
-

### Requirements
- Bolt 3.x installation
- [optional]

### Installation
1. Login to your Bolt installation
2. Go to "View/Install Extensions" (Hover over "Extras" menu item)
3. Type `bolt-simple-comment-system` into the input field
4. Click on the extension name
5. Click on "Browse Versions"
6. Click on "Install This Version" on the latest stable version

### Set up
1. You have to add `comment` ContentType. Connect to your admin, got to "Configuration/Contenttypes" and add the following :

 ```(yml)
comments:
    name: Comments
    singular_name: Comment
    fields:
        slug:
            type: slug
        user_displayname:
            type: text
        user_email:
            type: text
        body:
            type: html
            height: 300px
        linked_url:
            type: text
    recordsperpage: 100
    show_on_dashboard: true
    viewless: true
    sort: -datepublish
    default_status: publish
    icon_many: "fa:comments"
    icon_one: "fa:comment"
 ```

Then, you have update your database, got to "Configuration/Check database" and click on "Update the database" button.

### License
This Bolt extension is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

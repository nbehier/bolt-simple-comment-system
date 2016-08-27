### !!! Work in progress

Simple comment system - Bolt Extension
======================================

[Bolt](https://bolt.cm/) extension to add a simple local comment system

### Known limitations and futures features
- Comment Entity have to be create manually on contentypes.yml
- Notifications are not sent
- Add [emoji](https://github.com/Ranks/emojione)
- Add [mention.js](https://github.com/jakiestfu/Mention.js/)

### Requirements
- Bolt 3.x installation
- [optional] [Send email for new content](https://github.com/nbehier/bolt-sendemail-fornewcontent) to to send email to administrator when new comment is published

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
        author_display_name:
            type: text
            label: Display Name
        author_email:
            type: text
            label: Email
        body:
            type: html
            height: 300px
        linked_entity:
            type: text
            label: Parent entity
        notify:
            type: checkbox
            label: Keep author notify of new comments
    recordsperpage: 100
    show_on_dashboard: true
    viewless: true
    sort: -datepublish
    default_status: publish
    icon_many: "fa:comments"
    icon_one: "fa:comment"
 ```

2. Then, you have update your database, got to "Configuration/Check database" and click on "Update the database" button.

**Note:**
- This extension may uses the Swiftmailer library to send email notifications, based on the `mailoptions:` setting in your Bolt `app/config/config.yml` file.
- When first installed, Extension defaults to turning debugging on in the configuration. This should be turned off when deployed in production. When debugging is on, all outbound emails are sent to the configured debug email address.
- When you install Extension, you may have to create `app/config/extensions/boltsimplecommentsystem.leskis.yml`.

**Tip:** If you want to modify the HTML templates, you should copy the `.yml` file to your `theme/` folder, and modify it there. Any changes in the file in the distribution might be overwritten after an update to the extension. For instance, if you copy `list_comments.twig` to `theme/base-2016/my_list_comments.twig`, the corresponding line in `config.yml` should be: `list: my_list_comments.twig`

### Extension Configuration

```(yml)
features:
    list:
        order: asc # 'desc' if you want the new comments at the top
    gravatar:
        enabled: true
#        url: https://www.gravatar.com/avatar/XXX?s=40&d=mm
    debug:
        enabled: true
        address: noreply@example.com # email used to send debug notifications
    notify:
        enabled: true
        email:
            from_name:  Your website
            from_email: your-email@your-website.com
#            replyto_name:   #
#            replyto_email:  #

# templates:
# templates:
#     form: extensions/leskis/bolt-simple-comment-system/templates/form_comment.twig
#     list: extensions/leskis/bolt-simple-comment-system/templates/list_comments.twig
#     emailbody: extensions/leskis/bolt-simple-comment-system/templates/email_body.twig
#     emailsubject: extensions/leskis/bolt-simple-comment-system/templates/email_subject.twig

# assets:
#     frontend:
#         load_js: true
#         load_css: true
```

### License
This Bolt extension is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

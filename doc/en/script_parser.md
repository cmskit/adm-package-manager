# Parse, combine and compress HTML-Templates, Javascripts or CSS-Files

    Location: Tools -> Script parser/packer


**CSS-Packer** lets you concatenate and compress your backend-CSS-files. 
In addition variables are replaced by jQuery-UI values.

**Javascript-Packer** lets you concatenate and compress your backend-javascript-files. 
In addition some labels within the JS-files were translated.

**Template-Parser** lets you parse template files to callable php-/js-functions

## How it works

### HTML-templates

The tool looks for files with the mime type "xhtml". 

### Javascript + CSS (pack_xxx.json)


1. The tool looks for files with the names "pack_js.json" (if you want to pack your Javascripts) or "pack_css.json" (if you want to pack your CSS files)
2. If a pack-directive is picked it loads the instructions and 
3. tries to combine (and if the compress directive is set to true compress) all files found in "src"
4. the collection is saved to a file defined in "out"


Replacements of variables found in "path" + "out" or in the script: 

* 'DIR' is replaced by the actual directory of the json-file
* 'VENDOR' is replaced by the vendor path
* 'BACKEND' is replaced by the backend path
* 'FRONTEND' is replaced by the path of the actual project
* 'UI' is replaced by the jQuery-UI theme name [css packer] 
* 'LANG' is replaced by the actual language-shortcut [javascript packer] 



**Packing CSS**

(only if "lessify" is set to true)

Folders inside of "vendor/cmskit/jquery-ui/themes/" are taken as jQuery-UI Themes, so for every Theme a copy is made (in the path defined in "out") in order to replace variables with theme values.

Every theme folder should contain a file named "parameter.txt" with a url-encoded string of the theme parameters like this

	ffDefault=Helvetica%2CArial%2Csans-serif&fwDefault=normal&fsDefault=1.1em&cornerRadius=6px&...

You can find the parameters in the comment section of jquery-ui.css

Example: 

    {
        "lessify": true,
        "src": [
            {
                "path": "VENDOR/cmskit/jquery-ui/plugins/css/jquery.foldertree.css",
                "compress": true
            },
            {
                "path": "DIR/styles.css",
                "compress": true
            }
        ],
        "out": "DIR/packed_UI.css"
    }
    
**Packing Javascript**

Packing Javascript is driven by some directives

* "compress": compress the Javascript by removing whitespaces an linefeeds. You have to make sure that the script is "compressable" (e.g. statements and lines are termiated with a semicolon)
* "translate": all "underscore function calls" like "_('show_entry')" are replaced by a lacalized label found in the language-array in "TEMPLATE_FOLDER/locales/LANGUAGESHORTCUT.php"
"no_commenthead": if you want to supress the transfer of the first comment (usually containing licence informations) set this to true

Example:
    
    {
        "src": [
            {
                "path": "DIR/fn.*.js",
                "compress": true,
                "translate": true,
                "no_commenthead": true,
                "exclude": [
                    "fn.special_function.js",
                    "fn.another_function.js"
                ]
            },
            {
                "path": "DIR/core.js",
                "compress": true,
                "translate": true,
                "no_commenthead": true
            },
            {
                "path": "VENDOR/cmskit/jquery-ui/plugins/jquery.autosize.min.js",
                "compress": true,
                "translate": false,
                "no_commenthead": false
            },
            {
                "path": "VENDOR/cmskit/jquery-ui/plugins/jquery.foldertree.js",
                "compress": true,
                "translate": false,
                "no_commenthead": true
            }
        ],
        "out": "DIR/../packed_LANG.js"
    }


Note that you can use wildcards within the path (by using "*") to grab a bunch of files. 
In addition you can exclude some of the files from the list.






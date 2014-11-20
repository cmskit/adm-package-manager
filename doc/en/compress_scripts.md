# Combine and compress Javascripts or CSS-Files

There is an Tool to combine and compress Javascripts and CSS-Files, optional translating language labels or CSS variables.


## How it works

The Compressor 

1. looks into "backend/templates" or, if you have choosen "project extensions", "projects/PROJCTNAME/templates" looping all subfolders (templates)
2. in this template-folder it looks for a file config/packScripts.php checking for compressing instructions
3. according to the instructions it tries to add (and if the compress directive is set to true compress) a file found in "src"
4. the collection is saved to a files defined in "out"

Replacements of variables found in "path" + "out" or in the script: 

* 'TEMPLATE' is replaced by the actual template path
* 'VENDOR' is replaced by the vendor path
* 'BACKEND' is replaced by the backend path
* 'FRONTEND' is replaced by the path of the actual project
* 'UI' is replaced by the theme name

### packScripts.php


Here's a example configuration 


	{
	  "css": {
	    "lessify": true,
	    "src": [
	      {
		"path": "VENDOR/cmskit/jquery-ui/plugins/css/jquery.foldertree.css",
		"compress": true
	      },
	      {
		"path": "TEMPLATE/css/styles.css",
		"compress": true
	      }
	    ],
	    "out": "TEMPLATE/css/packed_UI.css"
	  },
	  "js": {
	    "src": [
	      {
		"path": "TEMPLATE/js/cmskit.core.js",
		"compress": true,
		"translate": true,
		"no_commenthead": true
	      },
	      {
		"path": "TEMPLATE/js/cmskit.desktop.js",
		"compress": true,
		"translate": true,
		"no_commenthead": false
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
	    "out": "TEMPLATE/js/packed_LANG.js"
	  }
	}


### Packing CSS (only if "lessify" is set to true)

Folders inside of "vendor/cmskit/jquery-ui/themes/" are taken as jQuery-UI Themes, so for every Theme a copy is made (in the path defined in "out") in order to replace variables with theme values.

Every theme folder should contain a file named "parameter.txt" with a url-encoded string of the theme parameters like this

	ffDefault=Helvetica%2CArial%2Csans-serif&fwDefault=normal&fsDefault=1.1em&cornerRadius=6px&...

You can find the parameters in the comment section of jquery-ui.css


### Packing Javascript

Packing Javascript is driven by some directives

* "compress": compress the Javascript by removing whitespaces an linefeeds. You have to make sure that the script is "compressable" (e.g. statements and lines are termiated with a semicolon)
* "translate": all "underscore function calls" like "_('show_entry')" are replaced by a lacalized label found in the language-array in "TEMPLATE_FOLDER/locales/LANGUAGESHORTCUT.php"
"no_commenthead": if you want to supress the transfer of the first comment (usually containing licence informations) set this to true





CakephpEmogrifierPlugin
=======================

CakephpEmogrifierPlugin is a CakePHP 2.0x Plugin that makes of using Emogrify on
your HTML output easy.

Wondering what Emogrify is?  Emogrifier is a great library from Pelago that deals
with much of the hassle involved with HTML formatted email messages:-

 - http://www.pelagodesign.com/sidecar/emogrifier/
 - https://github.com/jjriv/emogrifier

This Plugin is a wrapper around Emogrifier making it easy to use in CakePHP 2.0x


Install
-------

### Step 1
Copy or symlink CakephpEmogrifierPlugin into a path named Emogrifier in your Plugin
path like this:-

    %APP%/Plugin/Emogrifier

Take careful note of the Plugin pathname, the name is "Emogrifier", not 
EmogrifierPlugin or CakephpEmogrifierPlugin, it's just Emogrifier.  I spell this 
out because it's an easy thing to trip up on especially if your pulling this down 
from github or unpacking from a tarball.

### Step 2
Be sure to load the plugin in your bootstrap.php or core.php, like this:-

    CakePlugin::load('Emogrifier');

### Step 3.a (for the email case)
Tell CakeEmail to Emogrify the HTML rendering by calling viewRender()

    $email = new CakeEmail();
    $email->viewRender('Emogrifier.Emogrifier');
    $email->emailFormat('html');
    $email->send('hello world');

### Step 3.b (for web output, if you really wanted this...)
Tell your controller to render your view with Emogrifier like this:-

    $this->viewClass = 'Emogrifier.Emogrifier';


Questions and Answers
---------------------
Q: Pelago's Emogrifier class requires you to pass in the CSS but this plugin does
   not, what's going on?  
A: We parse the HTML from the View->output attribute looking for CSS from link
   and style elements then stich the whole thing together - it makes using this
   a little easier.

Q: I'm using Emogrifier before rendering to webpages and I can't see any difference  
A: Take a look at the HTML source, you should notice that all your CSS styles are
   now inline element style attributes.

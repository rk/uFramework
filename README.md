&micro;Framework
================

Version 1.0 RC3
---------------

Fixed some significant typos and improved the .htaccess file a *lot*. A few new features as well.

###New Features

  * Plugins and modules are now supported. In your controller use the method, `$this->require_modules('pChart', 'paypal');` to include `application/modules/pChart/module.php` and `application/modules/paypal/module.php`. In these files you can include all scripts necessary for the module/plugin to function.

###Typos

  * Default route would split parameters in the "glob" section when no glob section was matched. Now checked with `isset()`, not `empty()`. Also, it would not correctly parse 1-2 arguments.
  
  * Default route would possibly accept parameters with no specified action. Made the regex match against an optional action (with optional parameters) instead. Since regular expressions are usually greedy this shouldn't have been an issue, but I'm playing it safe.

###Improvements

  * The `redirect()` helper now supports almost all redirect status codes. 

  * The `redirect()` helper now defaults to a status of 303, which doesn't allow caching or resubmission of a page when redirecting (when POSTing).
  
  * A new `not_modified()` helper for when you want to force the client to keep their cached file.

  * No longer globbing and including everything in the includes folder on every request. Includes only config.php and helpers.php by default, and will autoload any classes you refer to as `application/includes/customcontroller.class.php`.
  
  * Now using Exceptions for better error handling, including error chaining.
  
###.htaccess
  
  * Now supports a RewriteBase parameter so that uFramework works in subdirectories by default.
  
  * Now protects the `application` folder and redirects all attempts to access it directly through `index.php`.
  
  * Now allows direct access to any file/directory that exists outside of the `application` folder.
  
  * Will now compress your javascript/css files by default.

Version 1.0 RC2
---------------

Well, it's been a long time since I worked to maintain Micro. Here's a bit of a changelog to detail what has changed between this and the previous release candidate.

  * The `link()` helper conflicted with a PHP function for creating a symbolic link. Renamed to `link_to()` instead.
  
  * The `absolute_path()` helper was rewritten slightly, it now URL Encodes the path information sent to it; this avoids the possibility of XSS attacks by the `link_to()` helper and any third-party helpers.
  
  * Now the `Controller->view()` method takes an optional third argument: layout. If you specify a layout to wrap the view in, the layout will be called with the same `$bind` variables, plus the rendered content from the view as a new `$yield` variable (make sure to echo this out in your layout). The resulting content is still returned to its caller.
  
  * Better exception handling for `Controller->view()`. Instead of returning a string when an error occurs, it now throws an exception of TemplateException type. If the template itself throws an exception it throws the same type of exception wrapping the previous exception in new one. The layout, if it raises an exception, will also have its exceptions wrapped in this same type of exception.
  
  * Started work on `__autoload()`. I should have done this before. When I finish transitioning away from the "include all" that occurs on the includes directory this should only load the `default.php` file and whatever classes you request. Helpers could be included differently, depending on what I decide.
  
  * Relative routing is now absolute routing to files. This should provide a little speedup on shared servers (as relative routing can be a total mess).

Version 1.0 RC1 is Here!
------------------------

This is good news and I'm almost unsure of if I should be excited or not. After almost two months of silence the internal, very tentative, heavily refactored and rewritten version 1.0 is nearly ready for release. Instead of a changelog, which would be too much to maintain over two months of chaotic programming, I may as well state what is the same as before.

The controller class hasn't changed, much. It is much slimmer now, though, and has only one method (`$this->view`) which used to be in an output class. I found that having a separate class for output brought no value. And that's where the similarities end.

Perhaps now you see why it's been taking so long. Backwards-compatibility is thoroughly broken, the documentation is no longer relevant, and I've been redesigning the project website to herald the change. There is still much to do, but that's why I'm only committing a release candidate for the moment.

Origins of &micro;Framework
---------------------------

The rationale behind the decision to create my own framework was simple: I'd done it before.  See, I took a bit of a loop around the MVC world and finally came back to my own work.

The original reason I left off using my own frameworks was because they were convoluted.  This I blame on a poor tutorial I read, which set me off on the wrong foot.  Looking back, it's no wonder I couldn't figure out how MVC truly worked.  But I digress.

I'd always wanted to try Ruby on Rails, and so once my first site was done I understood the concepts *much* better than before.  So, when I got into using Sinatra I fully comprehended how its metaprogramming generated a controller. When I came back to using PHP for various things I knew I couldn't find a Rails-like framework, or even a Sinatra-like one, just because of the language.  So, after trying CodeIgniter I declared that I'd write my own.

And so I have.

Design Principles
-----------------

The trouble with other PHP frameworks is that they're cluttered, obfuscated, or so bloated by additional functionality that I wanted to cut out all the flab.  This lead me to the following principles:

  1. Simplicity & Maintainability

     No framework of mine will reach into the thousands of lines of code, and so far this version of Micro is only in the 350's.  And, so far as I'm concerned, it's very nearly feature complete.  Since the project is so small and compact, and easily fits within a single file, this keeps it easy to troubleshoot.

  2. Performance

     The biggest thing slowing frameworks is their organization: many files.  What do you get when CodeIgniter uses some 20 includes/requires?  Why, whole bunches of hard drive seek time, of course.  So Micro will always be, at its core, only 1 file.  Thus organization within that file will be key.

  3. Stability

     Some frameworks have excellent stability, but through catch statements and many internal debugging steps.  I'd prefer to throw null's back at a function and have it be dealt with, than have to have exception handlers everywhere.  So, from the get-go I'm trying to write as much stability within the framework as possible.

     At this alpha stage normal use, and even some incorrect inputs, does not change the functionality of the framework.

Security is always a concern, so I've done a modicum of work towards that&mdash;mainly in ensuring that the input that becomes the names of classes and methods (and filenames) are all secure and not prone to security breaches.  And, because Micro is *simple* the entire rest is up to the application built on top of it.

Should You Use Micro?
---------------------

Micro will always be free for all uses, and is licensed under the [Creative Commons Attribution Share-Alike US license][cc]. (Whew!)

So, aside from generous licensing, why should you use Micro?

Micro isn't a product.  I develop Micro as a part of my own code-base, and maintain it for my own use.  Whether or not you, or anyone else, uses it or donates to it, Micro will always be maintained.

Micro is minimalist: it will *never* bloat.  The functionality included with Micro won't change unless necessary.  The code behind the framework may shrink or grow, but the core functionality will only change slowly.

Micro can be used internally at a corporation or business without any fees or legal snafus, just so long as no money is made from the derived software.

Micro was made by an individual for his own purposes.  When you use it you make the author happy to have made something good enough for your purposes.   And if you donate, then you know that you've made my day even if it is only $5.

  [cc]: http://creativecommons.org/licenses/by-sa/3.0/us/
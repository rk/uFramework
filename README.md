&micro;Framework
================

Version 1.0RC1 is Here!
--------------------

This is good news and I'm almost unsure of if I should be excited or not. After almost two months of silence the internal, very tentative, heavily refactored and rewritten version 1.0 is nearly ready for release. Instead of a changelog, which would be too much to maintain over two months of chaotic programming, I may as well state what is the same as before.

The controller class hasn't changed, much. It is much slimmer now, though, and has only one method (`$this->view`) which used to be in an output class. I found that having a seperate class for output brought no value. And that's where the similarities end.

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
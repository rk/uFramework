&micro;Framework
================

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

Micro will always be free for noncommercial use, and is licensed under the [Creative Commons Non-Commercial Share-Alike US license][cc].  (Whew!)  Alternative licensing is available by request, so contact me and we can negotiate something out.  If you have donated, or are considering it, contact me and I'll put in the physical mail a letter that is signed, granting you the use of &micro;Framework in whatever product you'd like to license it for&mdash;no "upgrade fees" necessary as development goes on.

So, that aside, why should you use Micro?

Micro isn't a product.  I develop Micro as a part of my own code-base, and maintain it for my own use.  Whether or not you, or anyone else, uses it or donates to it, Micro will always be maintained.

Micro is minimalist: it will *never* bloat.  The functionality included with Micro won't change unless necessary.  The code behind the framework may shrink or grow, but the core functionality will only change slowly.

Micro was made by an individual for his own purposes.  When you use it you make the author happy to have made something good enough for your purposes.   And if you donate, then you know that you've made my day even if it is only $5.

  [cc]: http://creativecommons.org/licenses/by-sa/3.0/us/
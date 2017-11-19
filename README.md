[BuildSize](https://buildsize.org/)
=========

[BuildSize](https://buildsize.org/) is a simple web app allowing you to automatically track the size of your build artifacts. For each pull request you receive, BuildSize will automatically calculate the size of the build artifacts, and leave a comment (and a build status message) comparing them to the latest master version.

This is currently a work-in-progress. More documentation (including documentation on how to self-host it) will be coming in the future.

If you want to hack on BuildSize, see [the development documentation](https://buildsize.org/docs/development).

Features
========
* **Simple to use**. BuildSize uses the artifacts from your existing CI system, so there's practically no configuration required, and it's available as a GitHub App so you don't need to install any software. Just install the GitHub App to your repository and that's it!
* **Supports CircleCI**, with more build systems coming soon.
* **Open-source**. Use the hosted version at https://buildsize.org/, or host it yourself.

Similar Projects
================
There are a few similar projects to BuildSize, such as [bundlesize](https://github.com/siddharthkp/bundlesize). [See how BuildSize differs](https://buildsize.org/docs/comparison.md).

Licence
=======
(The MIT licence)

Copyright (C) 2017 Daniel Lo Nigro (Daniel15)

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

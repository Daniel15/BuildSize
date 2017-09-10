<!-- https://buildsize.org/docs/comparison -->

BuildSize compared to others
============================

There are a few different systems that have goals and functionality similar to BuildSize. This pages aims to compare and contrast the systems. In the end, the choice is up to you - All of these systems are open-source so just use whichever one you like best :) 

BuildSize vs `siddharthkp/bundlesize`
-------------------------------------

| Feature | BuildSize | bundlesize |
| ------- | --------- | ------------
| License | Open-source (MIT) | Open-source (MIT) | 
| Installation | No extra software required | Requires Node.js and adding extra npm dependencies to your build |
| Build systems | Just supports CircleCI, with more to come | Can likely run anywhere, as it's not tied to specific CI systems |
| Setup | Very easy, just add a GitHub App | Manual configuration required. Need to manually copy and paste a GitHub access token into your build environment configuration. |
| Security | Runs as a GitHub webhook. No changes to build environment required. | Requires exposing a GitHub access token to your build environment, which could result in security issues if the token is leaked |
| GitHub API | Uses GitHub Apps API | Uses older GitHub OAuth API |

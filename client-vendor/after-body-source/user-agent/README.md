Update
======

#### [user-agent][ua] source code


`vendor` has been added as a git [subtree][subtree] and it can be updated like this:
```
git subtree pull --prefix=client-vendor/after-body-source/user-agent/vendor https://github.com/3rd-Eden/useragent.git <branch|tag> --squash
```

#### Regular Expressions

[user-agent][ua] is using a collection of regular expressions to parse the User Agent, defined in the [ua-parser][ua-parser] repository.

Steps to update the collection:
 
1. go in `vendor` folder
2. `npm install`
3. `./bin/update.js`


  [ua]: https://github.com/3rd-Eden/useragent
  [ua-parser]: https://github.com/ua-parser/uap-core
  [subtree]: https://developer.atlassian.com/blog/2015/05/the-power-of-git-subtree/


Usage
=====

```js
navigator.userAgent
> "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safari/537.36"
UserAgentParser.parse(navigator.userAgent)
> {
  	"family": "Chrome",
  	"major": "49",
  	"minor": "0",
  	"patch": "2623",
  	"device": {
  		"family": "Other"
  	},
  	"os": {
  		"family": "Mac OS X",
  		"major": "10",
  		"minor": "10",
  		"patch": "5"
  	}
  }
```

Current version
===============

- branch: https://github.com/zazabe/useragent/tree/browserfy
- forked from: https://github.com/3rd-Eden/useragent

Build User Agent 
================

1. clone User Agent repository  
```git clone <useragent-repo>.git```
2. update regular expressions (by using `https://raw.githubusercontent.com/ua-parser/uap-core/master/regexes.yaml`)
```./bin/update.js```
3. build the browserify version    
```
npm install 
browserify --standalone UserAgent browser.js > useragent.js
```
4. copy the User Agent build
```cp ./useragent.js <cm/client-vendor/after-body/useragent>/```


Usage
=====

```js
navigator.userAgent
> "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safari/537.36"
UserAgent.parse(navigator.userAgent)
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

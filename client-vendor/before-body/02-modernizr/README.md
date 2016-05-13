Current version
===============

- branch: https://github.com/zazabe/Modernizr/tree/audio-codec-opus 
- revision: cd05515
- note: see https://github.com/Modernizr/Modernizr/pull/1784

Build Modernizr 
===============

1. clone Modernizr repository  
```git clone <modernizr-repo>.git```
2. copy the config file  
```cp ./config <modernizr-path>/config.json```
3. build Modernizr with CM custom tests (in Modernizr repo)    
```
npm install 
./bin/modernizr -c config.json
```
4. copy the Modernizr build (in Modernizr repo)  
```cp ./modernizr.js <cm/client-vendor/before-body/01-modernizr>/```


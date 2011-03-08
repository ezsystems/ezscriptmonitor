<?php /* #?ini charset="utf-8"?

[ContentSettings]
# eZ Script Monitor implements its own handler for storing modifications made
# on a content class so that objects are not modified online. This might
# prevent timeout issues when many objects exists and/or on very high load
# web site.
ContentClassEditHandler=eZContentClassEditDeferredHandler

[RegionalSettings]
TranslationExtensions[]=ezscriptmonitor

*/ ?>

<?php

use Stichoza\GoogleTranslate\GoogleTranslate;

     function languages($language,$data){
        $tr = new GoogleTranslate();
        $languages = config('app.languages');

        if (in_array($language,$languages)){
            return $tr->setSource('en')->setTarget($language)->translate($data);
        }
        return response()->json('languages not support!');

}

<?php

(new CWidget())
    ->setTitle($data['title'])
    ->addItem(
        (new CDiv())
            ->addItem('DevKit module loaded successfully.')
    )
    ->show();

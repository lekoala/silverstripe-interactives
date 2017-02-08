<?php

/**
 * @author marcus
 */
class InteractiveControllerExtension extends Extension
{
    public function onAfterInit() {
        Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery.js');
        Requirements::javascript('advertisements/javascript/interactives.js');

        $url = $this->owner->getRequest()->getURL();

        $siteWide = InteractiveCampaign::get()->filter(['SiteWide' => 1]);
        
        $page = $this->owner->data();
        if ($page instanceof Page) {
            $pageCampaigns = InteractiveCampaign::get()->filterAny(['OnPages.ID' => $page->ID]);
        }

        $campaigns = array_merge($siteWide->toArray(), $pageCampaigns->toArray());

        $items = [];
        foreach ($campaigns as $campaign) {
            // collect its interactives.
            if (!$campaign->viewableOn($url, $page ? $page->class : null)) {
                continue;
            }

            $interactives = $campaign->relevantInteractives($url, $page);
            $items[] = array(
                'interactives' => $interactives,
                'display'       => $campaign->DisplayType,
                'id'            => $campaign->ID,
            );
        }

        $data = array(
            'endpoint'  => '',
            'trackviews'    => false,
            'trackclicks'   => true,
            'trackforward'  => true,
            'remember'      => false,
            'campaigns'     => $items,
            'tracker'       => '',
        );
        $data = json_encode($data);
        Requirements::customScript('window.SSInteractives = {config: ' . $data . '};', 'ads');
    }
}
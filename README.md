# Affiliate

A Drupal 10 module for tracking affiliate links.
Based heavily on **affiliate_ng** https://www.drupal.org/project/affiliate_ng with a few changes/improvements.

TODO: This documentation is in progress.

## INTRODUCTION

- 'Affiliates' are user accounts.
- The permission 'act as an affiliate' will give a user access to an 'Affiliate
  Center' dashboard on their profile at `/user/UID/affiliate`
- The global config settings are located at `/admin/config/affiliate/settings`
- There are also a handful permissions for creating campaigns and viewing stats on the permissions page, so make sure that's configured to your liking

This module creates the following 3 content entity types.

### affiliate_click ###

- Every visit to the site using an affiliate link
  creates an affiliate_click entity.
- When a click entity is created a cookie is saved on the visitors' device.
- This happens automatically, you shouldn't ever need to create a click
  entity yourself.

### affiliate_conversion ###

- When an action on the site occurs that you
  consider a "conversion", you need to create a conversion entity. The most
  obvious case for this is a commerce sale, but any action you want could be a conversion.

- Conversions are bundled entities, you can create different bundles/types of
  conversions with their own rules at `/admin/structure/affiliate/conversion/types`

- For Instance: a bundle named 'commerce_orders' could add conversions for
  orders
  while 'webform_submissions' could add conversions for
  submitting a
  webform, both awarding a different commission amount (or no commission at
  all if you just want to track how many times the action occurred).
- The main module itself does not
  automatically create conversions. The creation of conversions should be
  handled by
  submodules. For example, the included **commerce_affiliate** submodule
  creates conversions for commerce orders. But it's mainly left up to you to decide what you consider a 'conversion'

Example for creating a conversion.
```
// To get an affiliate user account and campaign saved in the cookie.
$affliateManager = \Drupal::service('affiliate.manager);
$affiliate = $affliateManager->getStoredAccount();
$campaign = $affliateManager->getStoredCampaign();

// Only create a conversion if we have an affiliate.
if($affiliate){

  $conversion = \Drupal::entityTypeManager('affiliate_conversion')->create(
    'type' => THE_CONVERSION_BUNDLE
    'affiliate' => $affiliate, // A user account
    'campaign' => $campaign,
  );

  // set an optional entity as a parent.
  // Typically this would be the entity that caused the conversion to be created.
  $conversion->setParentEntity($entity);

  // Set an optional comission amount,
  $conversion->setComission([
    'amount' => 123.99
    'currency' => 'USD'
  ]);

  // Set an optional label on the conversion
  $conversion->setLabel('Larry bought a t-shirt');

  $conversion->save();
}

```

### affiliate_campaign ###

- These act as categories to group where your
  affiliate clicks are coming from (in addition to the affiliate user account
  itself).
- These are fieldable.
- Campaigns can be either global or specific to an affiliate.
- When you install the module a default global campaign is created.
- You can create global campaigns at
  `/admin/config/affiliate/campaigns` or campaigns specific to your affiliate account at `/user/UID/affiliate/campaigns`





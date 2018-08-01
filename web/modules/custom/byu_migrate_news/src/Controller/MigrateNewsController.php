<?php

namespace Drupal\byu_migrate_news\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use \Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Term;
use Drupal\redirect\Entity\Redirect;
/**
 * Class MigrateImagesController.
 */
class MigrateNewsController extends ControllerBase {

  /**
   * Migrateimages.
   *
   * @return string
   *   Return Hello string.
   */
  public function migrateimages() {
    $output = "";
    /**
     * Clean out old media
     */
    //   $images_result = db_query("select fid from file_managed");
    //   foreach ($images_result as $imagerow) {
    //       $fid = $imagerow->fid;
    //       file_delete($fid);
    //   }
    $lastfid = 0;
    $images_result = db_query("select * from drupal7_news.file_managed order by fid");
    foreach ($images_result as $imagerow) {
      $fid = $imagerow->fid;
      $values = \Drupal::entityQuery('file')->condition('fid', $fid)->execute();
      $node_exists = !empty($values);
      if (!$node_exists) {
        $filename = $imagerow->filename;
        $uri = $imagerow->uri;
        if ($uri > "") {

        } else {
          $uri = " ";
        }
        $filemime = $imagerow->filemime;
        $filesize = $imagerow->filesize;
        $filetype = $imagerow->type;

        $image = File::create();
        $image->fid = $fid;
        $image->setFileUri($uri);
        $image->setMimeType($filemime);
        $image->setFileName($filename);
        $image->filesize = $filesize;
        $image->type = $filetype;
        $image->setPermanent();

        $photocredit = db_query("select field_credit_value from drupal7_news.field_data_field_credit where entity_id = $fid")->fetchField();

        if ($photocredit > "") {
          $query = \Drupal::entityQuery('taxonomy_term');
          $query->condition('vid', "photographers");
          $query->condition('name', $photocredit);
          $tids = $query->execute();

          if (empty($tids)) {
            $new_term = Term::create([
              'name' => $photocredit,
              'vid' => 'photographers',
            ])->save();
            $tids = $query->execute();
            $fieldtid = implode($tids);
          } else {

            $fieldtid = implode($tids);

          }



        }
        $cutline = db_query("select field_cutline_value from drupal7_news.field_data_field_cutline where entity_id = $fid")->fetchField();
        $image->save();
        $image->field_photo_credit[] = ['target_id' => $fieldtid];
        if ($cutline > "") {
          $image->field_cutline[] = ['value' => $cutline];
        }
        $image->save();
        $lastfid = $fid;
      }

    }
    if ($lastfid == 0) {
      $output .= "No images loaded.";
    } else {
      $output .= "Images Loaded...Last fid: " . $lastfid;
    }
    return [
      '#type' => 'markup',
      '#markup' => $this->t($output)
    ];
  }

  /**
   * DeleteTags.
   *
   * @return string
   *   Return Hello string.
   */
  public function deletetags() {
    $tids = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', 'tags')
      ->execute();

    $controller = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $entities = $controller->loadMultiple($tids);
    $controller->delete($entities);
    return [
      '#type' => 'markup',
      '#markup' => $this->t("Tags Deleted")
    ];
  }
  /**
   * CreateTags.
   *
   * @return string
   *   Return Hello string.
   */
  public function createtags() {
    //	$tids = \Drupal::entityQuery('taxonomy_term')
    //  	->condition('vid', 'tags')
    //	->execute();

    //	$controller = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    //	$entities = $controller->loadMultiple($tids);
    //	$controller->delete($entities);
    $tagcnt = 0;
    $sql_result = db_query("select distinct name from drupal7_news.taxonomy_term_data where vid = 1");
    foreach ($sql_result as $row) {
      $tagname = $row->name;
      if ($tagname > "") {
        $query = \Drupal::entityQuery('taxonomy_term');
        $query->condition('vid', "tags");
        $query->condition('name', $tagname);
        $tids = $query->execute();
        if (empty($tids)) {
          $new_term = Term::create([
            'name' => $tagname,
            'vid' => 'tags',
          ])->save();
          $tagcnt +=1;
        }
      }

    }
    $output = $tagcnt . " Tags Created.";
    return [
      '#type' => 'markup',
      '#markup' => $this->t($output)
    ];
  }
  /**
   * Migratestories.
   *
   * @return string
   *   Return Hello string.
   */
  public function migratestories() {
    $query = "select nid, title, created, status from drupal7_news.node where type = 'story' order by nid";
    $sql_result = db_query($query);
    $lastnid = 0;
    foreach ($sql_result as $row) {
      $nid = $row->nid;
      $values = \Drupal::entityQuery('node')->condition('nid', $nid)->execute();
      $node_exists = !empty($values);
      $moderationstate = 'draft';
      if (! $node_exists) {
        $status = $row->status;
        if ($status == 1) {
          $moderationstate = 'published';
        } else {
          $moderationstate = 'draft';
        }
        $created = $row->created;
        $published = date("Y-m-d", $created);
        $title = $row->title;
        if ($title > ""){

        } else {
          $title = "  ";
        }
        $bodytext = db_query("select body_value from drupal7_news.field_data_body where entity_id = $nid")->fetchField();
        $bodysummary = "";
        $bodysummary = db_query("select body_summary from drupal7_news.field_data_body where entity_id = $nid")->fetchField();
        $publicize = db_query("select field_publicize_value from drupal7_news.field_data_field_publicize where entity_id = $nid")->fetchField();
        $showonfront = 'false';
        if ($publicize == 'publicize') {
          $showonfront = 'true';
        }
        $node = Node::create([
          'type' => 'story',
          'uid' => 1,
          'langcode' => 'en',
          'nid' => $nid,
          'status' => $status,
          'title' => $title,
          'body' => [
            'value' => $bodytext,
            'format' => 'full_html',
            'summary' => $bodysummary,
          ],
          'field_show_on_front' => $showonfront,
          'field_publish_date' => $published,
        ]);
        $category = "";
        $categorytid = 0;
        $newcategorytid = 0;
        $categorytid = db_query("select field_newsletter_category_tid from drupal7_news.field_data_field_newsletter_category where entity_id = $nid")->fetchField();
        if ($categorytid > 0) {
          $category = db_query("select name from drupal7_news.taxonomy_term_data where tid = $categorytid")->fetchField();
          $newcategorytid = db_query("select tid from taxonomy_term_field_data where name = '$category'")->fetchField();
        } else {
          $newcategorytid = 0;
        }
        if ($newcategorytid > 0) {
          $node->field_category = ['target_id' => $newcategorytid];
        }
        $subhead = "";
        $subhead = db_query("select field_headline_value from drupal7_news.field_data_field_headline where entity_id = $nid")->fetchField();
        if ($subhead > "") {
          $node->field_subheading = $subhead;
        }
        $highlights = "";
        $highlights = db_query("select field_highlights_value from drupal7_news.field_data_field_highlights where entity_id = $nid")->fetchField();
        if ($highlights > "") {
          $node->field_story_highlights = ['value' => $highlights,
            'format' => 'full_html'];
        }
        $mediacontacttid = db_query("select field_media_contact_target_id from drupal7_news.field_data_field_media_contact where entity_id = $nid")->fetchField();
        $mediacontactname = "";
        if ($mediacontacttid > "") {
          $mediacontactname = db_query("select title from drupal7_news.node where nid = $mediacontacttid")->fetchField();
        }
        $mediauid = 0;
        $mediauid = db_query("select uid from users_field_data where name = '$mediacontactname'")->fetchField();
        if ($mediauid > 0) {
          $node->field_media_contact_byline_perso = ['target_id' => $mediauid];
        }
        $mediacontacttype = "";
        $mediacontacttype = db_query("select field_media_contact_type_value from drupal7_news.field_data_field_media_contact_type where entity_id = $nid")->fetchField();
        $fullname = "";
        $fullname = db_query("select field_first_name_value from drupal7_news.field_data_field_first_name where entity_id = $nid")->fetchField();
        if ($fullname > "") {
          $node->field_full_name = $fullname;
        }
        if ($mediacontacttype > "") {
          $node->field_media_contact_type = $mediacontacttype;
        }
        $writertype = "";
        $writername = "";
        $writertype = db_query("select field_writer_type_value from drupal7_news.field_data_field_writer_type where entity_id = $nid")->fetchField();
        if ($writertype == 'external') {
          $node->field_writer_type = $writertype;
          $writername = db_query("select field_writer_name_value from drupal7_news.field_data_field_writer_name where entity_id = $nid")->fetchField();
          $node->field_writer_name = $writername;
        }
        $authortid = 0;
        $authorname = "";
        $authortid = db_query("select field_author_target_id from drupal7_news.field_data_field_author where entity_id = $nid")->fetchField();
        if ($authortid > 0) {
          $authorname = db_query("select title from drupal7_news.node where nid = $authortid")->fetchField();
        }
        $authoruid = 0;
        $authoruid = db_query("select uid from users_field_data where name = '$authorname'")->fetchField();
        if ($authoruid > 0) {
          $node->field_writer_person_ = $authoruid;
        }

        $links_result = db_query("select field_link_url, field_link_title from drupal7_news.field_data_field_link where entity_id = $nid");
        foreach ($links_result as $linkrow) {
          $node->field_media_placement[] = ['uri' => $linkrow->field_link_url, 'title' => $linkrow->field_link_title];
        }
        $related_result = db_query("select field_related_target_id from drupal7_news.field_data_field_related where entity_id = $nid");
        foreach ($related_result as $relatedrow) {
          $relatedtid = $relatedrow->field_related_target_id;
          $node->field_related_stories[] = ['target_id' => $relatedtid];
        }

        $images_result = db_query("select field_photos_fid, field_photos_alt, field_photos_title from drupal7_news.field_data_field_photos where entity_id = $nid");
        foreach ($images_result as $imagerow) {
          $imagefid = $imagerow->field_photos_fid;
          $node->field_image[] = ['target_id' => $imagefid,
            'alt' => $imagerow->field_photos_alt,
            'title' => $imagerow->field_photos_title];
        }
        $thumb_result = db_query("select field_feature_image_title, field_feature_image_alt, field_feature_image_fid from drupal7_news.field_data_field_feature_image where entity_id = $nid");
        foreach ($thumb_result as $thumbrow) {
          $node->field_thumbnail[] = ['target_id' => $thumbrow->field_feature_image_fid,
            'alt' => $thumbrow->field_feature_image_alt,
            'title' => $thumbrow->field_feature_image_title];
        }
        $tags_result = db_query("select field_tags1_tid from drupal7_news.field_data_field_tags1 where entity_id = $nid");
        foreach ($tags_result as $tagsrow) {
          $tagid = $tagsrow->field_tags1_tid;
          $tagname = db_query("select name from drupal7_news.taxonomy_term_data where tid = $tagid")->fetchField();
          if ($tagname > "") {
            $query = \Drupal::entityQuery('taxonomy_term');
            $query->condition('vid', "tags");
            $query->condition('name', $tagname);
            $tids = $query->execute();
            $fieldtid = implode($tids);
            $node->field_tags[] = ['target_id' => $fieldtid];
          }
        }
        $node->save();
        $node->set('moderation_state',$moderationstate);
        $node->save();
        $lastnid = $nid;
      }

    }
    if ($lastnid == 0) {
      $output .= "No Stories Loaded.";
    } else {
      $output .= "Stories loaded...last nid: " . $nid;
    }
    return [
      '#type' => 'markup',
      '#markup' => $this->t($output)
    ];
  }
  public function deletenodes() {
    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(array('type' => 'story'));
    foreach ($nodes as $node) {
      $node->delete();
      echo "Node Deleted <br>";
    }
    return [
      '#type' => 'markup',
      '#markup' => $this->t("Nodes Deleted!")
    ];
  }
  public function deleteimages() {
    $images_result = db_query("select fid from file_managed");
    foreach ($images_result as $imagerow) {
      $fid = $imagerow->fid;

      file_delete($fid);
    }
    return [
      '#type' => 'markup',
      '#markup' => $this->t("Images Deleted!")
    ];
  }

  public function fixstatus() {
    $status_result = db_query("select nid from drupal7_news.node where status = 1");
    foreach ($status_result as $row) {
      $nid = $row->nid;
      $node = Node::load($nid);
      $node->set('moderation_state',"published");
      $node->save();
    }
    return [
      '#type' => 'markup',
      '#markup' => $this->t("Status Updated!")
    ];
  }
  public function addredirects() {
    //    $query = \Drupal::database()->select('drupal7_news.redirect', 'r');
    //    $query->addField('r', 'source');
    //    $query->addField('r', 'redirect');
    //    $query->condition('r.status', NodeInterface::PUBLISHED);
    //    $query->addTag('node_access');
    //    $redirect_result = $query->execute()->fetchAll();

    $redirect_query = "SELECT source,redirect FROM drupal7_news.redirect WHERE status = 1 and redirect NOT LIKE '%taxonomy/term%' and source not in (select redirect_source__path FROM drupal8_news.redirect )";
    $redirect_result = db_query($redirect_query);
    $redirectCount = 0;
    foreach ($redirect_result as $row) {
      $redirectUrl = $row->redirect;
      $redirectSource = $row->source;
      $redirect = Redirect::create();
      $redirect->setSource($redirectSource);
      $redirect->setRedirect($redirectUrl);
      $redirect->setLanguage('und');
      $redirect->setStatusCode(\Drupal::config('redirect.settings')->get('default_status_code'));
      $redirect->save();
      $redirectCount++;
    }
    $fix_entity_query = 'update redirect set redirect_redirect__uri = replace(redirect_redirect__uri,"internal:/node","entity:node")';
    $fix_entity_result = db_query($fix_entity_query);
    $fix_external_query= 'update redirect set redirect_redirect__uri = replace(redirect_redirect__uri,"internal:/http","http")';
    $fix_external_result = db_query($fix_external_query);
    return [
      '#type' => 'markup',
      '#markup' => $this->t($redirectCount . " Redirects Created!")
    ];
  }

  /**
   * this is not working yet and it doesn't truly matter since I can truncate the table by hand
   */
  //  public function removeallredirects() {
  //    $result = \Drupal::database()->truncate('redirect')->execute();
  //    return [
  //      '#type' => 'markup',
  //      '#markup' => $this->t("All Redirects Deleted! " . $result)
  //    ];
  //  }
}
<?php
/**
 * Iframe embed controller.  NDF, 22 March 2011.
 *
 * @copyright Copyright 2011 The Open University.
 */

class Embed extends CI_Controller {

  /** OUVLE player embed.
  */
  public function vle() {
    header('Content-Type: text/html; charset=utf-8');

    // Process the request.
    $req = (object) array(
    // Required.
      'media_url' => $this->input->get('media_url'),
      'title'     => $this->_required('title'),
      'width'     => $this->_required('width'),  # is_numeric. Required?
      'height'    => $this->_required('height'), # Play height, not media(?)
    // Optional.
      'image_url' => $this->input->get('image_url'),
      'caption_url'=>$this->input->get('caption_url'),
      'lang' => $this->input->get('lang'), #Just a reminder!
    );

    if (preg_match('/learn.open.ac.uk.*\.(mp4|flv|mp3)$/', $req->media_url, $ext)) {
      // Codecs? http://wiki.whatwg.org/wiki/Video_type_parameters
      $opts = array('mp4'=>'video', 'flv'=>'video', 'mp3'=>'audio');
      $req->media_type = $opts[$ext[1]];
      $req->html5 = 'flv'!=$ext[1];
    } else {
      $this->_error("'media_url' is a required parameter. (Accepts URLs ending mp4, flv and mp3.)", 400);
    }
    if ($req->caption_url && !preg_match('/\.(srt|xml|ttml)$/', $req->caption_url)) {
      $this->_error("'caption_url' accepts URLs ending srt, xml and ttml.", 400);
    }
    $base_url = dirname($req->media_url);
    $req->image_url  = $this->_absolute($req->image_url, $base_url);
    $req->caption_url= $this->_absolute($req->caption_url, $base_url);

    $this->load->view('vle_player', $req);
  }


  /** Handle errors.
  */
  protected function _error($message, $code=500) {
    @header("HTTP/1.1 $code");
    die("$code. Error, $message");
  }

  /** Handle required GET parameters. */
  protected function _required($param) {
    $value = $this->input->get($param);
    if (!$value) {
      $this->_error("'$param' is a required URL parameter.", 400);
    }
    return $value;
  }

  /** Make relative URLs absolute. */
  protected function _absolute($url, $base_url) {
    if ($url && !parse_url($url, PHP_URL_SCHEME)) {
      return $base_url.'/'.$url;
    }
    return $url;
  }
}
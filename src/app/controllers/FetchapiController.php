<?php
session_start();
use Phalcon\Mvc\Controller;

class FetchapiController extends Controller
{
    public function indexAction()
    {
        $endpoint = "https://openlibrary.org/search.json?q=";
        $book = $this->request->getPost('book');
        $book = str_replace(' ', '+', $book);
        $url = $endpoint . $book . "&mode=ebooks&has_fulltext=true";

        $ch = curl_init();
        //grab the URL and pass it to the variable.
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = (curl_exec($ch));
        $data = json_decode($data, true);
        curl_close($ch);
        $this->session->set('content', $data);
        $this->response->redirect('fetchapi/displayall');
    }
    public function displayallAction()
    {
        $data = $this->session->get('content');
        $output = "";
        foreach ($data['docs'] as $value) {
            $languages = count($value['language']);
            $isbn = $value['isbn'][0];
            $languages = max($languages, 1);
            $output .= "<section style=\"background-color: #eee;\">
            <div class=\"container py-5\">
            <div class=\"row justify-content-center\">
                <div class=\"col-md-12 col-xl-10\">
                    <div class=\"card shadow-0 border rounded-3\">
                        <div class=\"card-body\">
                            <div class=\"row\">
                                <div class=\"col-md-12 col-lg-3 col-xl-3 mb-4 mb-lg-0\">
                                    <div class=\"bg-image hover-zoom ripple rounded ripple-surface\">
                                        <img src=\"//covers.openlibrary.org/b/id/$value[cover_i]-M.jpg\"
                                            class=\"w-100\" />
                                        <a href=\"#!\">
                                            <div class=\"hover-overlay\">
                                    <div class=\"mask\" style=\"background-color: rgba(253, 253, 253, 0.15);\">
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class=\"col-md-6 col-lg-6 col-xl-6\">
                                    <!-- title of book -->
                                    <h5>$value[title]</h5>
                                    <!-- authors -->
                                    <p>By : ";
            foreach ($value['author_name'] as $name) {
                $output .= $name . ', ';
            }
            $output .= "</p>
                                    <!-- first published in year -->
                                    <p>First published in year : $value[first_publish_year]</p>
                                    <!-- #editions in #languages -->
                                    <p>$value[edition_count] editions in $languages languages</p>
                                </div>
                                <div class=\"col-md-6 col-lg-3 col-xl-3 border-sm-start-none border-start\">
                                    <div class=\"d-flex flex-column mt-4\">";
            $output .= $this->tag->linkTo(["fetchapi/single?isbn=$isbn", 'Details', 'class' => 'btn btn-primary']);
            $output .= "</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>";
        }
        $this->view->data = $output;
    }

    public function singleAction()
    {
        $isbn = $_GET['isbn'];
        $endpoint = "https://openlibrary.org/api/books?bibkeys=ISBN:$isbn&jscmd=details&format=json";
        $ch = curl_init();
        //grab the URL and pass it to the variable.
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = (curl_exec($ch));
        $data = json_decode($data, true);
        curl_close($ch);
        $value = "ISBN:" . $isbn;
        $arr = $data[$value];
        $details = $arr['details'];
        $preview = $arr['preview_url'] . '/mode/2up?view=theater';
        // improves the quality of image
        $image = str_replace('S', 'L', $arr['thumbnail_url']);
        $output = "";
        $output .= "<section style=\"background-color: #eee;\">
        <div class=\"container py-5\">
          <div class=\"row justify-content-center\">
            <div class=\"col-md-8 col-lg-6 col-xl-4\">
              <div class=\"card text-black\">
                <i class=\"fab fa-apple fa-lg pt-3 pb-1 px-3\"></i>
                <img src=\"$image\"
                  class=\"card-img-top\" alt=\"$arr[title]\" />
                <div class=\"card-body\">
                  <div class=\"text-center\">
                    <h5 class=\"card-title\">$details[title]</h5>
                    <p>";
        $output .= $details['notes']['value'];

        $output .= "</p>
                  </div>
                  <div>
                    <div class=\"d-flex justify-content-between\">
                      <span>Author : </span><span>";
        $output .= $details['authors'][0]['name'];
        $output .= "</span>
        <span>Publisher : </span><span>";
        $output .= $details['publisher'][0];
        $output .= "</span>
                    </div>";

        $output .= "<a href = \"$preview\" class = 'btn btn-primary'>Read More</a>";

        $output .= "</div>
              </div>
            </div>
          </div>
        </div>
      </section>";
        $this->view->data = $output;
    }
}

<?php
/**
 * Class AnidbAnime
 * @brief Anime Setup
 * @author gloire
 * @author libero1i
 * @link https://anisekai.com
 * @version 1.3
 * @since September 2020
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
namespace AniDB\Anime;
use DOMDocument;

require_once __DIR__ . '/options.php';

final class Anime extends Options
{
    /**
     * @param $cloud_name
     * @param $key
     * @param $secret
     * @brief set Cloudinary Image CDN
     */
    public function setCloudinary($cloud_name, $key, $secret){
        \Cloudinary::config(array(
            "cloud_name" => $cloud_name,
            "api_key" => $key,
            "api_secret" => $secret,
            "secure" => true
        ));
    }

    public function setAnimeID($aid)
    {
        $this->aid = $aid;
        $this->setURL();
    }

    public function setURL()
    {
        $this->url = parent::MAIN_URL . '/anime/' . $this->aid;
    }

    /**
     * @param $html
     * @brief set HTML source
     */
    public function setHTML($html)
    {
        $this->html = $html;
    }

    public function controlAndGetTitle()
    {
        preg_match(parent::PATTERN_TITLE, $this->html, $titleArr);
        $this->title = $titleArr[1];
        if (!isset($this->title) || empty($this->title)) {
            return 'Not Found!';
            exit();
        }
        return $this->title;
    }

    /**
     * @return mixed|string
     * @brief getting Image
     */
    public function getImage()
    {
        $image = $this->patternMaker(parent::PATTERN_IMAGE, $this->html);
        $this->image = $image[2];
        $arr_result = \Cloudinary\Uploader::upload($this->image);
        $this->image = $arr_result['url'];
        return $this->image;
    }

    public function getEpisode()
    {
        $episode = $this->patternMaker(parent::PATTERN_EPISODE, $this->html);
        $this->episode = $episode[1];
        return $this->episode;
    }

    public function getTags()
    {
        preg_match_all(parent::PATTERN_TAGS, $this->html, $tags);
        if (empty($tags[2][0])) {
            $this->tags = ['-', 'N/A'];
            return $this->tags;
        }
        $firstTag = $tags[2][0];
        preg_match_all(parent::PATTERN_SECOND_TAG, $tags[3][0], $tags);
        array_unshift($tags[1], $firstTag);
        $tags = $tags[1];
        $this->tags = $tags;
        return $this->tags;
    }

    public function getStartDate()
    {
        $startDate = $this->patternMaker(parent::PATTERN_START_DATE, $this->html);
        $this->startDate = $startDate[2];
        return $this->startDate;
    }

    public function getEndDate()
    {
        $endDate = $this->patternMaker(parent::PATTERN_END_DATE, $this->html);
        $this->endDate = $endDate[2];
        return $this->endDate;
    }

    public function getRating()
    {
        $rating = $this->patternMaker(parent::PATTERN_RATING, $this->html);
        $this->rating = $rating[1];
        return $this->rating;
    }

    public function getDescription()
    {
        $description = $this->patternMaker(parent::PATTERN_DESCRIPTION, $this->html);
        $this->description = $description[1];
        return $this->description;
    }

    public function getFavourites()
    {
        $favourites = $this->patternMaker(parent::PATTERN_FAVOURITES, $this->html);
        $this->favourites = $favourites[1];
        return $this->favourites;
    }

    public function getRank()
    {
        $rank = $this->patternMaker(parent::PATTERN_RANK, $this->html);
        $this->rank = $rank[1];
        return $this->rank;
    }

    public function getCompleted()
    {
        $completed = $this->patternMaker(parent::PATTERN_COMPLETED, $this->html);
        $this->completed = $completed[2];
        return $this->completed;
    }

    public function getWatching()
    {
        $watching = $this->patternMaker(parent::PATTERN_WATCHING, $this->html);
        $this->watching = $watching[2];
        return $this->watching;
    }

    public function getPlan()
    {
        $plan = $this->patternMaker(parent::PATTERN_PLAN, $this->html);
        $this->plan = $plan[2];
        return $this->plan;
    }

    public function getDrop()
    {
        $drop = $this->patternMaker(parent::PATTERN_DROP, $this->html);
        $this->drop = $drop[2];
        return $this->drop;
    }

    public function getDirectlyRelatedAnimes()
    {
        preg_match_all(parent::PATTERN_DIRECTLY_RELATED, $this->html, $directArr);
        if (empty($directArr[0])) {
            preg_match_all(parent::PATTERN_CONTROL_DIRECTLY_RELATED, $this->html, $directArr);
        }
        if (!empty($directArr[0])) {
            $del = explode('/', $directArr[3][0]);
            $firstKey = end($del);
            $firstValue = $directArr[4][0];

            $str = implode("", $directArr[5]);
            preg_match_all(parent::PATTERN_SECOND_DIRECTLY_RELATED, $str, $directlyRelatedArray);

            array_unshift($directlyRelatedArray[1], $firstKey);
            array_unshift($directlyRelatedArray[2], $firstValue);
            $arrCount = count($directlyRelatedArray[1]);

            for ($i = 1; $i < $arrCount; $i++) {
                $delimitier = explode('/', $directlyRelatedArray[1][$i]);
                $directlyRelatedArray[1][$i] = end($delimitier);
            }
            $key = $directlyRelatedArray[1];
            $value = $directlyRelatedArray[2];
            $directlyRelatedArray = array_combine($key, $value);
            $this->directylyRelatedAnime = $directlyRelatedArray;
            return $this->directylyRelatedAnime;
        } else {
            $this->directylyRelatedAnime = ['-', 'N/A'];
            return $this->directylyRelatedAnime;
        }
    }

    public function getSimilarAnimes()
    {
        preg_match_all(parent::PATTERN_SIMILAR, $this->html, $similarArray);
        if (!empty($similarArray[0])) {
            $del = explode('/', $similarArray[3][0]);
            $firstKey = end($del);
            $firstValue = $similarArray[4][0];

            $str = implode("", $similarArray[5]);
            preg_match_all(parent::PATTERN_SECOND_SIMILAR, $str, $similarArray);
            array_unshift($similarArray[2], $firstKey);
            array_unshift($similarArray[3], $firstValue);
            $arrCount = count($similarArray[2]);
            for ($i = 1; $i < $arrCount; $i++) {
                $delimitier = explode('/', $similarArray[2][$i]);
                $similarArray[2][$i] = end($delimitier);
            }

            $key = $similarArray[2];
            $value = $similarArray[3];

            $similarArray = array_combine($key, $value);
            $this->similarAnimes = $similarArray;
            return $this->similarAnimes;
        } else {
            $this->similarAnimes = ['-', 'N/A'];
            return $this->similarAnimes;
        }
    }

    public function getCast()
    {
        preg_match_all(parent::PATTERN_CAST, $this->html, $castArray);
        if (!empty($castArray[0])) {
            $firstCastId = $castArray[2][0];
            $firstCastName = $castArray[3][0];
            $firstCastCharId = $castArray[5][0];
            $firstCastCharName = $castArray[6][0];

            $firstArray = [
                $firstCastId => [
                    $firstCastName,
                    $firstCastCharId,
                    $firstCastCharName
                ]
            ];
            preg_match_all(parent::PATTERN_SECOND_CAST, $castArray[7][0], $cast);
            $values = [];
            $castCount = count($cast[2]);
            $newCast = [];
            for ($i = 0; $i < $castCount; $i++) {

                $arr = [
                    $cast[3][$i],
                    $cast[5][$i],
                    $cast[6][$i]
                ];
                array_push($values, $arr);
            }

            $keys = [];
            for ($i = 0; $i < $castCount; $i++) {
                array_push($keys, $cast[2][$i]);
            }

            $cast = array_combine(array_values($keys), $values);
            array_unshift($cast, $firstArray[$firstCastId]);
            $this->cast = $cast;
            return $this->cast;
        } else {
            $this->cast = ['-', 'N/A'];
            return $this->cast;
        }
    }

    public function getStaff()
    {
        preg_match_all(parent::PATTERN_STAFF, $this->html, $exArray);
        if (isset($exArray[1][0]) && isset($exArray[2][0])) {
            $firstId = $exArray[1][0];
            $firstName = $exArray[2][0];
        } else {
            $firstId = '';
            $firstName = '';
        }
        if (empty($firstId) || empty($firstName)) {
            $this->staff = ['-', 'N/A'];
            return $this->staff;
        } else {
            preg_match_all(parent::PATTERN_SECOND_STAFF, $this->html, $arr);
            preg_match_all(parent::PATTERN_MAIN_STAFF, $arr[2][0], $arr2);
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            @ $doc->loadHTML($arr[2][0]);
            libxml_clear_errors();
            $tr = $doc->getElementsByTagName('tr');
            $keys = [];
            $values = [];
            $Staff = [];
            $i = 0;
            foreach ($tr as $t){
                $link = new DOMDocument();
                libxml_use_internal_errors(true);
                $link->loadHTML($arr2[1][$i]);
                libxml_clear_errors();
                $a = $link->getElementsByTagName('a');
                $creatorId = $a[0]->getAttribute('href');
                $creatorId = explode('=', $creatorId);
                $creatorId = end($creatorId);
                array_push($keys, $creatorId);
                $creatorName = $link->textContent;
                $count = $a->length;
                $arr3 = [];
                $k = 0;
                foreach ($a as $href){
                    if ($k !== 0){
                        $staffId = $href->getAttribute('href');
                        $staffId = explode('/', $staffId);
                        $boo = in_array('virtual', $staffId);
                        if ($boo == 1){
                            $boo = 'true';
                        }else{
                            $boo = 'false';
                        }
                        $staffId = end($staffId);
                        $arr4 = [
                            'virtual' => $boo,
                            'id' => $staffId,
                            'name' => $href->textContent
                        ];
                        array_push($arr3, $arr4);
                    }
                    $k++;
                }
                $staffArr = [
                    'creator' => $creatorName,
                    'staff' => array_values($arr3),
                ];
                array_push($values, $staffArr);
                $i++;
            }
            $Staff = array_combine(array_values($keys), $values);
            $this->staff = $Staff;
            return $this->staff;
        }
    }

    public function getCharacters()
    {
        preg_match_all(parent::PATTERN_CHARACTERS, $this->html, $mainCharArray);
        if (!empty($mainCharArray[0])) {
            $firstCharId = $mainCharArray[7][0];
            $firstCharName = $mainCharArray[8][0];
            $firstCharImage = $mainCharArray[3][0];
            $firstCharEpisodes = $mainCharArray[5][0];
            $firstCharDesc = $mainCharArray[10][0];
            $firstCharStaffId = $mainCharArray[12][0];
            $firstCharStaffName = $mainCharArray[13][0];

            preg_match_all(parent::PATTERN_SECOND_CHARACTERS, $mainCharArray[14][0], $mainCharArray);
            array_unshift($mainCharArray[6], $firstCharId);
            array_unshift($mainCharArray[2], $firstCharImage);
            array_unshift($mainCharArray[4], $firstCharEpisodes);
            array_unshift($mainCharArray[7], $firstCharName);
            array_unshift($mainCharArray[9], $firstCharDesc);
            array_unshift($mainCharArray[11], $firstCharStaffId);
            array_unshift($mainCharArray[12], $firstCharStaffName);

            $charImageArray = $mainCharArray[2];
            $charEpisodesArray = $mainCharArray[4];
            $charIdArray = $mainCharArray[6];
            $charNamesArray = $mainCharArray[7];
            $charDescArray = $mainCharArray[9];
            $charStaffIdArray = $mainCharArray[11];
            $charStaffNamesArray = $mainCharArray[12];

            $arrCount = count($charIdArray);

            $values = [];
            for ($i = 0; $i < $arrCount; $i++) {
                $img = \Cloudinary\Uploader::upload($charImageArray[$i]);
                $img = $img['url'];
                $arr = [
                    $charNamesArray[$i],
                    $img,
                    $charEpisodesArray[$i],
                    $charDescArray[$i],
                    $charStaffIdArray[$i],
                    $charStaffNamesArray[$i]
                ];
                array_push($values, $arr);
            }

            $keys = $charIdArray;

            $char = array_combine($keys, $values);
            $this->characters = $char;
            return $this->characters;
        } else {
            $this->characters = ['-', 'N/A'];
            return $this->characters;
        }
    }
}

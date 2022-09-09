<?php

/**
 *
 */
class GithubUsers
{
    private const TOKEN = "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";
    private const INT_VALUE_FIELDS = ["id", "public_repos", "public_gists", "followers", "following"];
    private const FLOAT_VALUE_FIELDS = [];
    private const BOOL_VALUE_FIELDS = ["site_admin", "hireable"];

    /**
     * @var string
     */
    private string $userName;
    /**
     * @var array
     */
    private static array $userData = [];
    /**
     * @var array
     */
    private array $userFieldsArray;
    /**
     * @var string
     */
    private string $htmlProfile;

    /**
     * @param   string  $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if (array_key_exists($name, static::$userData)) {
            return static::$userData[$name];
        }

        return null;
    }

    /**
     * @param   string  $userName
     * @param   array   $userFieldsArray
     */
    public function __construct(string $userName, array $userFieldsArray)
    {
        $this->userName        = $userName;
        $this->userFieldsArray = $userFieldsArray;
    }

    /**
     * @return array
     */
    public static function getUserData(): array
    {
        return static::$userData;
    }

    /**
     * @return string|bool
     */
    public function getUserByUserName(): string|bool
    {
        if(time() - $_COOKIE["lastUserHandle"] >= 3600){
            $ch = curl_init("https://api.github.com/users/" . $this->userName);

            curl_setopt($ch, CURLOPT_HEADER, "Accept: application/vnd.github+json");
            curl_setopt($ch, CURLOPT_HEADER, "Authorization: Bearer " . static::TOKEN);
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


            $result = curl_exec($ch);

            $reqInfo = curl_getinfo($ch);
            if ($reqInfo["http_code"] == 200) {
                if ($result = json_decode($result, true)) {
                    foreach ($this->userFieldsArray as $key) {
                        static::$userData[$key] = match (true) {
                            in_array($key, static::INT_VALUE_FIELDS) => intval($result[$key]),
                            in_array($key, static::FLOAT_VALUE_FIELDS) => floatval($result[$key]),
                            in_array($key, static::BOOL_VALUE_FIELDS) => boolval($result[$key]),
                            default => strval($result[$key]),
                        };
                    }

                    setcookie("lastUserHandle", time());
                    $_SESSION["userData"] = static::$userData;

                    return true;
                }

                return "Decode json error";
            } else {
                if ($err = curl_error($ch)) {
                    return $err;
                }

                curl_close($ch);

                return $result;
            }
        }
        else {
            static::$userData = $_SESSION["userData"];
            return true;
        }
    }

    /**
     * @return bool|string
     */
    public function getProfileCard(): bool|string
    {
        if (array_key_exists("html_url", static::$userData)):
            $ch = curl_init(static::$userData["html_url"]);
            ob_start();
            curl_exec($ch);
            $this->htmlProfile = ob_get_clean();
            return $this->htmlProfile;
        else:
            return "Profile url not found";
        endif;
    }
}
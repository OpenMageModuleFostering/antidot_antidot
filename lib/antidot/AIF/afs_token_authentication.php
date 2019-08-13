<?php
require_once 'AIF/afs_authentication.php';

/** @brief AFS token authentication.
 *
 * Instances of this class should be used t interact with Antidot Back Office
 * APIs.
 */
class AfsTokenAuthentication implements AfsAuthentication
{
    private $token = null;


    /** @brief Constructs new AFS token authentication instance.
     * @param $token [in] Authentication token.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /** @brief Formats authentication parameters.
     * @param $version [in] Format string representation according to provided
     *        version information.
     * @return array representing authentication.
     */
    public function format_as_url_param()
    {
        return array();
    }
    /** @brief Formats authentication parameters.
     * @param $version [in] Format string representation according to provided
     *        version information.
     * @return array representing authentication.
     */
    public function format_as_header_param()
    {
        return array('access-token' => $this->token);
    }
}

<?php

namespace TPerformant\API;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\UriFactoryDiscovery;
use Http\Client\Common\PluginClient;
use Http\Client\Common\Plugin\ErrorPlugin;
use TPerformant\API\Exception\ConnectionException;
use TPerformant\API\Exception\APIException;
use TPerformant\API\HTTP\ApiResponse;
use TPerformant\API\HTTP\AuthInterface;
use TPerformant\API\Filter\AdvertiserProgramFilter;
use TPerformant\API\Filter\AdvertiserProgramSort;
use TPerformant\API\Filter\AdvertiserCommissionFilter;
use TPerformant\API\Filter\AdvertiserCommissionSort;
use TPerformant\API\Filter\AdvertiserAffiliateFilter;
use TPerformant\API\Filter\AdvertiserAffiliateSort;
use TPerformant\API\Filter\AffiliateProgramFilter;
use TPerformant\API\Filter\AffiliateProgramSort;
use TPerformant\API\Filter\AffiliateCommissionFilter;
use TPerformant\API\Filter\AffiliateCommissionSort;
use TPerformant\API\Filter\AffiliateProductFeedFilter;
use TPerformant\API\Filter\AffiliateProductFeedSort;
use TPerformant\API\Filter\AffiliateProductFilter;
use TPerformant\API\Filter\AffiliateProductSort;
use TPerformant\API\Filter\AffiliateBannerFilter;
use TPerformant\API\Filter\AffiliateBannerSort;
use TPerformant\API\Filter\AffiliateAdvertiserPromotionFilter;
use TPerformant\API\Filter\AffiliateAdvertiserPromotionSort;

/**
 * API wrapper class
 */
class Api {
    const WRAPPER_VERSION = '1.0';
    const API_VERSION = '1.0.1';

    private $apiUrl;

    private $http = null;
    private $messageFactory = null;
    private $uriFactory = null;

    private $httpOptions = [];


    /**
     * Constructor
     * @param string $baseUrl   The base URL for API calls
     * @param array $options    Configuration options
     */
    public function __construct($baseUrl = 'https://api.2performant.com', $options = []) {
        $this->apiUrl = $baseUrl;

        $httpOptions = [
            'headers' => [
                'User-Agent' => 'TP-PHP-API:' . __CLASS__ . '-v' . self::WRAPPER_VERSION,
                'Content-Type' => 'application/json'
            ]
        ];

        if(isset($options['http']) && is_array($options['http']))
            $httpOptions = array_merge($httpOptions, $options['http']);

        if(!isset($httpOptions['timeout']) || 0 == $httpOptions['timeout'])
            $httpOptions['timeout'] = 5.0;

        $this->httpOptions = $httpOptions;

        $errorPlugin = new ErrorPlugin();

        $this->http = new PluginClient(
            HttpClientDiscovery::find(),
            [$errorPlugin]
        );
        $this->messageFactory = MessageFactoryDiscovery::find();
        $this->uriFactory = UriFactoryDiscovery::find();
    }

    /**
     * Authentication method
     * @param  string $email    User email address
     * @param  string $password User password
     *
     * @return ApiResponse
     */
    public function signIn($email, $password) {
        return $this->post('/users/sign_in', [
            'user' => [
                'email' => $email,
                'password' => $password
            ]
        ], 'user');
    }

    /**
     * Validate a set of credentials
     * @param  AuthInterface $auth Authentication credentials
     * @return User                The user details, if the credentials are correct
     */
    public function validateToken(AuthInterface $auth) {
        return $this->get('/users/validate_token', [], 'user', $auth);
    }

    /**
     * Get a quicklink for an affiliate in a program
     * @param  string           $url        The destination of the quicklink
     * @param  Affiliate|string $affiliate  The affiliate who owns the quicklink. Either an Affiliate object or its unique code
     * @param  Program|string   $program    The program for which the quicklink is generated. Either a Program object or its unique code
     *
     * @return string           The generated quicklink
     */
    public function getQuicklink($url, $affiliate, $program) {
        $host = preg_replace('/((?:https?\:)?\/\/.*)api((?:\.[a-zA-Z0-9\-]+)*)(\.2performant\.com)/', '$1event$2$3', $this->apiUrl);
        if(is_a($affiliate, '\\TPerformant\\API\\Model\\Affiliate'))
            $affiliate = $affiliate->getUniqueCode();
        if(is_a($program, '\\TPerformant\\API\\Model\\Program'))
            $program = $program->getUniqueCode();

        return sprintf(
            '%s/events/click?ad_type=quicklink&aff_code=%s&unique=%s&redirect_to=%s',
            $host,
            urlencode($affiliate),
            urlencode($program),
            urlencode($url)
        );
    }

    // Public methods


    // Advertiser methods

    /**
     * Get affiliate program list as an advertiser
     * @param  AuthInterface            $auth   The authentication token container
     * @param  AdvertiserProgramFilter  $filter (optional) Result filtering options
     * @param  AdvertiserProgramSort    $sort   (optional) Result sorting options
     *
     * @return ApiResponse
     */
    public function getAdvertiserPrograms(AuthInterface $auth, AdvertiserProgramFilter $filter = null, AdvertiserProgramSort $sort = null) {
        $params = [];
        if($filter)
            $params = array_merge($params, $filter->toParams());

        if($sort)
            $params = array_merge($params, $sort->toParams());

        return $this->get('/advertiser/programs', $params, 'programs', $auth);
    }

    /**
     * Get a single affiliate program as an advertiser
     * @param  AuthInterface $auth The authentication token container
     * @param  int|string    $id   The ID or slug of the program
     *
     * @return ApiResponse
     */
    public function getAdvertiserProgram(AuthInterface $auth, $id) {
        return $this->get('/advertiser/programs/'.$id, [], 'program', $auth);
    }

    /**
     * Get own commission list as an advertiser
     * @param  AuthInterface                $auth   The authentication token container
     * @param  AdvertiserCommissionFilter   $filter (optional) Result filtering options
     * @param  AdvertiserCommissionSort     $sort   (optional) Result sorting options
     *
     * @return ApiResponse
     */
    public function getAdvertiserCommissions(AuthInterface $auth, AdvertiserCommissionFilter $filter = null, AdvertiserCommissionSort $sort = null) {
        $params = [];
        if($filter)
            $params = array_merge($params, $filter->toParams());

        if($sort)
            $params = array_merge($params, $sort->toParams());

        return $this->get('/advertiser/programs/default/commissions', $params, 'commissions', $auth);
    }

    /**
     * Get a single commission as an advertiser
     * @param  AuthInterface    $auth   The authentication token container
     * @param  int|string       $id     The ID of the commission
     *
     * @return ApiResponse
     */
    public function getAdvertiserCommission(AuthInterface $auth, $id) {
        return $this->get('/advertiser/programs/default/commissions/'.$id, [], 'commission', $auth);
    }

    /**
     * Create a manual commission for an affiliate as an advertiser
     * @param  AuthInterface    $auth        The authentication token container
     * @param  int|string       $affiliateId The affiliate's ID
     * @param  int|float        $amount      The commission amount, in EUR
     * @param  string           $description The commission's description
     *
     * @return ApiResponse
     */
    public function createAdvertiserCommission(AuthInterface $auth, $affiliateId, $amount, $description) {
        $params = [
            'commission' => [
                'user_id' => $affiliateId,
                'amount' => $amount,
                'description' => $description
            ]
        ];

        return $this->post('/advertiser/programs/default/commissions', $params, 'commission', $auth);
    }

    /**
     * Edit a commission's amount as an advertiser
     * @param  AuthInterface    $auth           The authentication token container
     * @param  int|string       $id             The commission's ID
     * @param  string           $reason         A reason for the modification
     * @param  int|float|Array  $newAmount      The new commission amount. If numeric, currency is considered to be EUR. If array, it must have `amount` and `currencyCode`
     * @param  string           $newDescription (optional) The new commission description, if it's the case
     *
     * @return ApiResponse
     */
    public function editAdvertiserCommission(AuthInterface $auth, $id, $reason, $newAmount, $newDescription = null) {
        if(is_numeric($newAmount)) {
            $newAmount = [
                'amount' => $newAmount,
                'currencyCode' => null
            ];
        } else {
            if(!is_array($newAmount)) {
                throw new TPException('Fourth argument of Api::editAdvertiserCommission() must be a number or an array');
            }
        }

        $params = [
            'commission' => [
                'reason' => $reason,
                'amount' => $newAmount['amount'],
                'currency_code' => $newAmount['currencyCode'] ?: 'EUR'
            ]
        ];

        if($newDescription) {
            $params['commission']['description'] = $newDescription;
        }

        return $this->put('/advertiser/programs/default/commissions/'.$id, $params, 'commission', $auth);
    }

    /**
     * Mark an own commission as accepted, as an advertiser
     * @param  AuthInterface $auth   The authentication token container
     * @param  int|string    $id     The commission's ID
     * @param  string        $reason (optional) The reason for accepting the commission
     *
     * @return ApiResponse
     */
    public function acceptAdvertiserCommission(AuthInterface $auth, $id, $reason = '') {
        $params = [
            'commission' => [
                'current_reason' => $reason
            ]
        ];

        return $this->put('/advertiser/programs/default/commissions/'.$id.'/accept', $params, 'commission', $auth);
    }

    /**
     * Mark an own commission as rejected, as an advertiser
     * @param  AuthInterface $auth   The authentication token container
     * @param  int|string    $id     The commission's ID
     * @param  string        $reason The reason for rejecting the commission
     *
     * @return ApiResponse
     */
    public function rejectAdvertiserCommission(AuthInterface $auth, $id, $reason) {
        $params = [
            'commission' => [
                'current_reason' => $reason
            ]
        ];

        return $this->put('/advertiser/programs/default/commissions/'.$id.'/reject', $params, 'commission', $auth);
    }

    /**
     * Get own affiliate list as an advertiser
     * @param  AuthInterface                $auth   The authentication token container
     * @param  AdvertiserAffiliateFilter    $filter (optional) Result filtering options
     * @param  AdvertiserAffiliateSort      $sort   (optional) Result sorting options
     *
     * @return ApiResponse
     */
    public function getAdvertiserAffiliates(AuthInterface $auth, AdvertiserAffiliateFilter $filter = null, AdvertiserAffiliateSort $sort = null) {
        $params = [];
        if($filter)
            $params = array_merge($params, $filter->toParams());

        if($sort)
            $params = array_merge($params, $sort->toParams());

        return $this->get('/advertiser/programs/default/affiliates', $params, 'affiliates', $auth);
    }

    /**
     * Get a single affiliate as an advertiser
     * @param  AuthInterface    $auth   The authentication token container
     * @param  string           $id     The unique code of the affiliate
     *
     * @return ApiResponse
     */
    public function getAdvertiserAffiliate(AuthInterface $auth, $id) {
        return $this->get('/advertiser/programs/default/affiliates/'.$id, [], 'affiliate', $auth);
    }

    /**
     * Get tracking code information for own program
     * @param  AuthInterface    $auth   The authentication token container
     *
     * @return ApiResponse
     */
    public function getAdvertiserTrackingCode(AuthInterface $auth) {
        return $this->get('/advertiser/programs/default/tracking_code', [], 'campaign', $auth, 'tracking_code');
    }


    // Affiliate methods

    /**
     * Get affiliate program list as an Affiliate
     * @param  AuthInterface            $auth   The authentication token container
     * @param  AffiliateProgramFilter   $filter (optional) Result filtering options
     * @param  AffiliateProgramSort     $sort   (optional) Result sorting options
     *
     * @return ApiResponse
     */
    public function getAffiliatePrograms(AuthInterface $auth, AffiliateProgramFilter $filter = null, AffiliateProgramSort $sort = null) {
        $params = [];
        if($filter)
            $params = array_merge($params, $filter->toParams());

        if($sort)
            $params = array_merge($params, $sort->toParams());

        return $this->get('/affiliate/programs', $params, 'programs', $auth);
    }

    /**
     * Get a single affiliate program as an affiliate
     * @param  AuthInterface $auth The authentication token container
     * @param  int|string    $id   The program's ID or slug
     *
     * @return ApiResponse
     */
    public function getAffiliateProgram(AuthInterface $auth, $id) {
        return $this->get('/affiliate/programs/'.$id, [], 'program', $auth);
    }

    /**
     * Get the affiliate request infor for a certain program, as an affiliate
     * @param  AuthInterface $auth The authentication token container
     * @param  int|string    $id   The program's ID or slug
     *
     * @return ApiResponse
     */
    public function getAffiliateRequest(AuthInterface $auth, $id) {
        return $this->get('/affiliate/programs/'.$id.'/me', [], 'affrequest', $auth);
    }

    /**
     * Get own commissions as an affiliate
     * @param  AuthInterface                $auth   The authentication token container
     * @param  AffiliateCommissionFilter    $filter (optional) Result filtering options
     * @param  AffiliateCommissionSort      $sort   (optional) Result sorting options
     *
     * @return ApiResponse
     */
    public function getAffiliateCommissions(AuthInterface $auth, AffiliateCommissionFilter $filter = null, AffiliateCommissionSort $sort = null) {
        $params = [];
        if($filter)
            $params = array_merge($params, $filter->toParams());

        if($sort)
            $params = array_merge($params, $sort->toParams());

        return $this->get('/affiliate/commissions', $params, 'commissions', $auth);
    }

    /**
     * Get product feeds as an affiliate
     * @param  AuthInterface                $auth   The authentication token container
     * @param  AffiliateProductFeedFilter   $filter (optional) Result filtering options
     * @param  AffiliateProductFeedSort     $sort   (optional) Result sorting options
     *
     * @return ApiResponse
     */
    public function getAffiliateProductFeeds(AuthInterface $auth, AffiliateProductFeedFilter $filter = null, AffiliateProductFeedSort $sort = null) {
        $params = [];
        if($filter)
            $params = array_merge($params, $filter->toParams());

        if($sort)
            $params = array_merge($params, $sort->toParams());

        return $this->get('/affiliate/product_feeds', $params, 'product_feeds', $auth);
    }

    /**
     * Get products from a product feed as an affiliate
     * @param  AuthInterface            $auth   The authentication token container
     * @param  int|string               $id     Product feed's ID
     * @param  AffiliateProductFilter   $filter (optional) Result filtering options
     * @param  AffiliateProductSort     $sort   (optional) Result sorting options
     *
     * @return ApiResponse
     */
    public function getAffiliateProducts(AuthInterface $auth, $id, AffiliateProductFilter $filter = null, AffiliateProductSort $sort = null) {
        $params = [];
        if($filter)
            $params = array_merge($params, $filter->toParams());

        if($sort)
            $params = array_merge($params, $sort->toParams());

        return $this->get('/affiliate/product_feeds/'.$id.'/products', $params, 'products', $auth);
    }

    /**
     * Get banners as an affiliate
     * @param  AuthInterface            $auth   The authentication token container
     * @param  AffiliateBannerFilter    $filter (optional) Result filtering options
     * @param  AffiliateBannerSort      $sort   (optional) Result sorting options
     *
     * @return ApiResponse
     */
    public function getAffiliateBanners(AuthInterface $auth, AffiliateBannerFilter $filter = null, AffiliateBannerSort $sort = null) {
        $params = [];
        if($filter)
            $params = array_merge($params, $filter->toParams());

        if($sort)
            $params = array_merge($params, $sort->toParams());

        return $this->get('/affiliate/banners', $params, 'banners', $auth);
    }

    /**
     * Get promotions as an affiliate
     * @param  AuthInterface            $auth   The authentication token container
     * @param  AffiliateAdvertiserPromotionFilter    $filter (optional) Result filtering options
     * @param  AffiliateAdvertiserPromotionSort      $sort   (optional) Result sorting options
     *
     * @return ApiResponse
     */
    public function getAffiliatePromotions(AuthInterface $auth, AffiliateAdvertiserPromotionFilter $filter = null, AffiliateAdvertiserPromotionSort $sort = null) {
        $params = [];
        if($filter)
            $params = array_merge($params, $filter->toParams());

        if($sort)
            $params = array_merge($params, $sort->toParams());

        return $this->get('/affiliate/advertiser_promotions', $params, 'advertiser_promotions', $auth);
    }


    // General request method

    /**
     * Make an API request
     * @param  string           $method         One of GET, POST, PUT, DELETE
     * @param  string           $route          The API endpoint to be requested
     * @param  array            $params         Associative array of parameters
     * @param  string           $expected       Expected object key in response hash
     * @param  AuthInterface    $auth           The authentication token container. Not needed for sign in requests
     * @param  string           $overrideClass  If the expected object does not signal the class properly, use this as a replacement
     *
     * @return ApiResponse
     */
    public function request($method, $route, $params, $expected, AuthInterface $auth = null, $overrideClass = null) {
        $url = $this->uriFactory->createUri($this->getUrl($route));

        $headers = [];

        // authentication headers
        if($auth) {
            $headers = [
                'access-token' => $auth->getAccessToken(),
                'client' => $auth->getClientToken(),
                'uid' => $auth->getUid()
            ];
        }

        $request = null;

        // request body
        if('GET' === $method) {
            $url = $url->withQuery(http_build_query($params));
            $request = $this->prepareRequest($this->messageFactory->createRequest($method, $url, $headers));
        } else {
            $request = $this->prepareRequest($this->messageFactory->createRequest($method, $url, $headers, json_encode($params)));
        }



        try {
            $response = $this->http->sendRequest($request);
        } catch(\Http\Client\Common\Exception\ServerErrorException $e) {
            throw \TPerformant\API\Exception\ServerException::create($e);
        } catch(\Http\Client\Common\Exception\ClientErrorException $e) {
            // do nothing, validation will be performed later
            $response = $e->getResponse();
        } catch(\Http\Client\Exception\NetworkException $e) {
            throw \TPerformant\API\Exception\ConnectionException::create($e);
        } catch(\Http\Client\Exception\TransferException $e) {
            throw new \TPerformant\API\Exception\TransferException($e->getMessage(), $e->getCode());
        }

        return new ApiResponse($response, $expected, $auth, $overrideClass);
    }

    /**
     * Shorthand for API GET request
     * @param  string           $route          The API endpoint to be requested
     * @param  array            $params         Associative array of parameters
     * @param  string           $expected       Expected object key in response hash
     * @param  AuthInterface    $auth           The authentication token container. Not needed for sign in requests
     * @param  string           $overrideClass  If the expected object does not signal the class properly, use this as a replacement
     *
     * @return ApiResponse
     */
    public function get($route, $params, $expected, AuthInterface $auth = null, $overrideClass = null) {
        return $this->request('GET', $route, $params, $expected, $auth, $overrideClass);
    }

    /**
     * Shorthand for API POST request
     * @param  string           $route          The API endpoint to be requested
     * @param  array            $params         Associative array of parameters
     * @param  string           $expected       Expected object key in response hash
     * @param  AuthInterface    $auth           The authentication token container. Not needed for sign in requests
     * @param  string           $overrideClass  If the expected object does not signal the class properly, use this as a replacement
     *
     * @return ApiResponse
     */
    public function post($route, $params, $expected, AuthInterface $auth = null, $overrideClass = null) {
        return $this->request('POST', $route, $params, $expected, $auth, $overrideClass);
    }

    /**
     * Shorthand for API PUT request
     * @param  string           $route          The API endpoint to be requested
     * @param  array            $params         Associative array of parameters
     * @param  string           $expected       Expected object key in response hash
     * @param  AuthInterface    $auth           The authentication token container. Not needed for sign in requests
     * @param  string           $overrideClass  If the expected object does not signal the class properly, use this as a replacement
     *
     * @return ApiResponse
     */
    public function put($route, $params, $expected, AuthInterface $auth = null, $overrideClass = null) {
        return $this->request('PUT', $route, $params, $expected, $auth, $overrideClass);
    }

    /**
     * Shorthand for API DELETE request
     * @param  string           $route          The API endpoint to be requested
     * @param  array            $params         Associative array of parameters
     * @param  string           $expected       Expected object key in response hash
     * @param  AuthInterface    $auth           The authentication token container. Not needed for sign in requests
     * @param  string           $overrideClass  If the expected object does not signal the class properly, use this as a replacement
     *
     * @return ApiResponse
     */
    public function delete($route, $params, $expected, AuthInterface $auth = null, $overrideClass = null) {
        return $this->request('DELETE', $route, $params, $expected, $auth, $overrideClass);
    }

    /**
     * Final URL constructor for an API endpoint
     * @param  string   $route Requested API endpoint
     *
     * @return string   Full URL of the API endpoint
     */
    protected function getUrl($route) {
        // TODO extend this in case we switch to versioned URLs
        return $this->uriFactory->createUri($this->apiUrl . $route . '.json');
    }

    /**
     * API wrapper signature information decorator
     * @param  \Psr\Http\Message\RequestInterface   $request Request to be decorated
     *
     * @return \Psr\Http\Message\RequestInterface   The request signed by the API wrapper
     */
    protected function prepareRequest($request) {
        if(array_key_exists('headers', $this->httpOptions) && is_array($this->httpOptions['headers'])) {
            foreach($this->httpOptions['headers'] as $key => $value) {
                $request = $request->withHeader($key, $value);
            }
        }
        return $request;
    }


    // Singleton stuff

    /**
     * Singleton object
     * @var Api
     */
    private static $_api = null;

    /**
     * Get an instance of the Api
     * @return Api  The singleton object
     */
    public static function getInstance() {
        if(null === self::$_api) {
            self::$_api = new self();
        }

        return self::$_api;
    }

    /**
     * Construct and initialize the Api singleton object
     * @param  string   $baseUrl    The base URL for API calls
     * @param  array    $options    Configuration options
     *
     * @return Api
     */
    public static function init($baseUrl, $options = []) {
        self::$_api = new self($baseUrl, $options);
    }
}

<?php

namespace League\Url\Test;

use League\Url\Url;
use League\Url\Scheme;
use League\Url\User;
use League\Url\Pass;
use League\Url\Host;
use League\Url\Port;
use League\Url\Path;
use League\Url\Query;
use League\Url\Fragment;
use PHPUnit_Framework_TestCase;

/**
 * @group url
 */
class UrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Url
     */
    private $url;

    const BASE_URL = "http://a/b/c/d;p?q";

    public function setUp()
    {
        $this->url = Url::createFromUrl(
            'http://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3'
        );
    }

    public function tearDown()
    {
        $this->url = null;
    }

    public function testGetterAccess()
    {
        $this->assertInstanceof('League\Url\Scheme', $this->url->getScheme());
        $this->assertInstanceof('League\Url\User', $this->url->getUser());
        $this->assertInstanceof('League\Url\Pass', $this->url->getPass());
        $this->assertInstanceof('League\Url\Host', $this->url->getHost());
        $this->assertInstanceof('League\Url\Port', $this->url->getPort());
        $this->assertInstanceof('League\Url\Path', $this->url->getPath());
        $this->assertInstanceof('League\Url\Query', $this->url->getQuery());
        $this->assertInstanceof('League\Url\Fragment', $this->url->getFragment());
    }

    public function testImmutabilityAccess()
    {
        $this->assertEquals($this->url, $this->url->withScheme('http'));
        $this->assertEquals($this->url, $this->url->withUserInfo('login', 'pass'));
        $this->assertEquals($this->url, $this->url->withHost('secure.example.com'));
        $this->assertEquals($this->url, $this->url->withPort(443));
        $this->assertEquals($this->url, $this->url->withPath('/test/query.php'));
        $this->assertEquals($this->url, $this->url->withQuery('kingkong=toto'));
        $this->assertEquals($this->url, $this->url->withFragment('doc3'));
    }

    public function testGetBaseUrl()
    {
        $this->assertSame('http://login:pass@secure.example.com:443', $this->url->getBaseUrl());
    }

    public function testGetAuthority()
    {
        $this->assertSame('login:pass@secure.example.com:443', $this->url->getAuthority());
    }

    public function testGetUserInfo()
    {
        $this->assertSame('login:pass', $this->url->getUserInfo());
    }

    public function testAutomaticUrlNormalization()
    {
        $url = Url::createFromUrl(
            'HtTpS://MaStEr.eXaMpLe.CoM:443/%7ejohndoe/%a1/index.php?foo.bar=value#fragment'
        );

        $this->assertSame(
            'https://master.example.com/~johndoe/%A1/index.php?foo.bar=value#fragment',
            (string) $url
        );
    }

    public function testForceUrlNormalization()
    {
        $url = Url::createFromUrl(
            'HtTpS://MaStEr.eXaMpLe.CoM:83/%7ejohndoe/%a1/../index.php?foo.bar=value#fragment'
        );

        $this->assertSame(
            'https://master.example.com:83/~johndoe/index.php?foo.bar=value#fragment',
            (string) $url->normalize()
        );
    }

    public function testToArray()
    {
        $url = Url::createFromUrl('https://toto.com:443/toto.php');
        $this->assertSame([
            'scheme' => 'https',
            'user' => null,
            'pass' => null,
            'host' => 'toto.com',
            'port' => 443,
            'path' => '/toto.php',
            'query' => null,
            'fragment' => null,
        ], $url->toArray());
    }

    public function testEmptyConstructor()
    {
        $url = new Url(
            new Scheme(),
            new User(),
            new Pass(),
            new Host(),
            new Port(),
            new Path(),
            new Query(),
            new Fragment()
        );

        $this->assertEmpty($url->__toString());
    }

    public function testSameValueAs()
    {
        $url1 = new Url(
            new Scheme(),
            new User(),
            new Pass(),
            new Host('example.com'),
            new Port(),
            new Path(),
            new Query(),
            new Fragment()
        );

        $url2 = new Url(
            new Scheme(),
            new User(),
            new Pass(),
            new Host('ExAmPLe.cOm'),
            new Port(),
            new Path(),
            new Query(),
            new Fragment()
        );

        $this->assertTrue($url1->sameValueAs($url2));
    }

    /**
     * @param $relative
     * @param $expected
     *
     * @dataProvider resolveProvider
     */
    public function testResolve($url, $relative, $expected)
    {
        $this->assertSame($expected, (string) Url::createFromUrl($url)->resolve($relative));
    }

    public function resolveProvider()
    {
        return [
          'baseurl' =>                 [self::BASE_URL, "",               self::BASE_URL],
          'scheme' =>                  [self::BASE_URL, "ftp://d/e/f",    "ftp://d/e/f"],
          'path 1' =>                  [self::BASE_URL, "g",              "http://a/b/c/g"],
          'path 2' =>                  [self::BASE_URL, "./g",            "http://a/b/c/g"],
          'path 3' =>                  [self::BASE_URL, "g/",             "http://a/b/c/g/"],
          'path 4' =>                  [self::BASE_URL, "/g",             "http://a/g"],
          'authority' =>               [self::BASE_URL, "//g",            "http://g"],
          'query' =>                   [self::BASE_URL, "?y",             "http://a/b/c/d;p?y"],
          'path + query' =>            [self::BASE_URL, "g?y",            "http://a/b/c/g?y"],
          'fragment' =>                [self::BASE_URL, "#s",             "http://a/b/c/d;p?q#s"],
          'path + fragment' =>         [self::BASE_URL, "g#s",            "http://a/b/c/g#s"],
          'path + query + fragment' => [self::BASE_URL, "g?y#s",          "http://a/b/c/g?y#s"],
          'single dot 1'=>             [self::BASE_URL, ".",              "http://a/b/c/"],
          'single dot 2' =>            [self::BASE_URL, "./",             "http://a/b/c/"],
          'single dot 3' =>            [self::BASE_URL, "./g/.",          "http://a/b/c/g/"],
          'single dot 4' =>            [self::BASE_URL, "g/./h",          "http://a/b/c/g/h"],
          'double dot 1' =>            [self::BASE_URL, "..",             "http://a/b/"],
          'double dot 2' =>            [self::BASE_URL, "../",            "http://a/b/"],
          'double dot 3' =>            [self::BASE_URL, "../g",           "http://a/b/g"],
          'double dot 4' =>            [self::BASE_URL, "../..",          "http://a/"],
          'double dot 5' =>            [self::BASE_URL, "../../",         "http://a/"],
          'double dot 6' =>            [self::BASE_URL, "../../g",        "http://a/g"],
          'double dot 7' =>            [self::BASE_URL, "../../../g",     "http://a/g"],
          'double dot 8' =>            [self::BASE_URL, "../../../../g",  "http://a/g"],
          'double dot 9' =>            [self::BASE_URL, "g/../h" ,        "http://a/b/c/h"],
          'mulitple slashes' =>        [self::BASE_URL, "foo////g",       "http://a/b/c/foo////g"],
          'complex path 1' =>          [self::BASE_URL, ";x",             "http://a/b/c/;x"],
          'complex path 2' =>          [self::BASE_URL, "g;x",            "http://a/b/c/g;x"],
          'complex path 3' =>          [self::BASE_URL, "g;x?y#s",        "http://a/b/c/g;x?y#s"],
          'complex path 4' =>          [self::BASE_URL, "g;x=1/./y",      "http://a/b/c/g;x=1/y"],
          'complex path 5' =>          [self::BASE_URL, "g;x=1/../y",     "http://a/b/c/y"],
          'origin url without path' => ["http://a",     "b/../y",         "http://a/y"],
        ];
    }
}

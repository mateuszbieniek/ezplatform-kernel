<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\BinaryFile\Type as BinaryFileType;
use eZ\Publish\Core\FieldType\BinaryFile\Value as BinaryFileValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\SPI\FieldType\BinaryBase\RouteAwarePathGenerator;

/**
 * @group fieldType
 * @group ezbinaryfile
 *
 * @covers \eZ\Publish\Core\FieldType\BinaryFile\Type
 */
class BinaryFileTest extends BinaryBaseTest
{
    /**
     * Returns the field type under test.
     *
     * This method is used by all test cases to retrieve the field type under
     * test. Just create the FieldType instance using mocks from the provided
     * get*Mock() methods and/or custom get*Mock() implementations. You MUST
     * NOT take care for test case wide caching of the field type, just return
     * a new instance from this method!
     *
     * @return \eZ\Publish\Core\FieldType\FieldType
     */
    protected function createFieldTypeUnderTest(): FieldType
    {
        $fieldType = new BinaryFileType(
            [$this->getBlackListValidatorMock()],
            $this->getRouteAwarePathGenerator()
        );
        $fieldType->setTransformationProcessor($this->getTransformationProcessorMock());

        return $fieldType;
    }

    protected function getEmptyValueExpectation()
    {
        return new BinaryFileValue();
    }

    public function provideInvalidInputForAcceptValue()
    {
        $baseInput = parent::provideInvalidInputForAcceptValue();
        $binaryFileInput = [
            [
                new BinaryFileValue(['id' => '/foo/bar']),
                InvalidArgumentValue::class,
            ],
        ];

        return array_merge($baseInput, $binaryFileInput);
    }

    public function provideValidInputForAcceptValue()
    {
        return [
            [
                null,
                new BinaryFileValue(),
            ],
            [
                new BinaryFileValue(),
                new BinaryFileValue(),
            ],
            [
                [],
                new BinaryFileValue(),
            ],
            [
                __FILE__,
                new BinaryFileValue(
                    [
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'downloadCount' => 0,
                        'mimeType' => null,
                    ]
                ),
                [/* 'getFileSize' => filesize( __FILE__ ) */],
                [/* 'getMimeType' => 'text/plain' */],
            ],
            [
                ['inputUri' => __FILE__],
                new BinaryFileValue(
                    [
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'downloadCount' => 0,
                        'mimeType' => null,
                    ]
                ),
                [/*'getFileSize' => filesize( __FILE__ ) */],
                [/* 'getMimeType' => 'text/plain' */],
            ],
            [
                [
                    'inputUri' => __FILE__,
                    'fileSize' => 23,
                ],
                new BinaryFileValue(
                    [
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => 23,
                        'downloadCount' => 0,
                        'mimeType' => null,
                    ]
                ),
                [],
                [/* 'getMimeType' => 'text/plain' */],
            ],
            [
                [
                    'inputUri' => __FILE__,
                    'downloadCount' => 42,
                ],
                new BinaryFileValue(
                    [
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'downloadCount' => 42,
                        'mimeType' => null,
                    ]
                ),
                [/* 'getFileSize' => filesize( __FILE__ ) */],
                [/* 'getMimeType' => 'text/plain' */],
            ],
            [
                [
                    'inputUri' => __FILE__,
                    'mimeType' => 'application/text+php',
                ],
                new BinaryFileValue(
                    [
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'downloadCount' => 0,
                        'mimeType' => 'application/text+php',
                    ]
                ),
                [/* 'getFileSize' => filesize( __FILE__ ) */],
            ],
            // BC with 5.2 (EZP-22808). Id can be used as input instead of inputUri.
            [
                ['id' => __FILE__],
                new BinaryFileValue(
                    [
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'downloadCount' => 0,
                        'mimeType' => null,
                    ]
                ),
            ],
        ];
    }

    /**
     * Provide input for the toHash() method.
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to toHash(), 2. The expected return value from toHash().
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          null,
     *          null
     *      ),
     *      array(
     *          new BinaryFileValue( array(
     *              'id' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ) ),
     *          array(
     *              'id' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInputForToHash()
    {
        return [
            [
                new BinaryFileValue(),
                null,
            ],
            [
                new BinaryFileValue(
                    [
                        'id' => 'some/file/here',
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                        'uri' => 'http://some/file/here',
                    ]
                ),
                [
                    'id' => 'some/file/here',
                    'inputUri' => null,
                    'path' => null,
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                    'uri' => 'http://some/file/here',
                ],
            ],
            [
                new BinaryFileValue(
                    [
                        'inputUri' => 'some/file/here',
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                        'uri' => 'http://some/file/here',
                    ]
                ),
                [
                    'id' => null,
                    'inputUri' => 'some/file/here',
                    // Used for BC with 5.0 (EZP-20948)
                    'path' => 'some/file/here',
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                    'uri' => 'http://some/file/here',
                ],
            ],
            // BC with 5.0 (EZP-20948). Path can be used as input instead of inputUri.
            [
                new BinaryFileValue(
                    [
                        'path' => 'some/file/here',
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                        'uri' => 'http://some/file/here',
                    ]
                ),
                [
                    'id' => 'some/file/here',
                    'inputUri' => null,
                    'path' => null,
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                    'uri' => 'http://some/file/here',
                ],
            ],
            // BC with 5.0 (EZP-20948). Path can be used as input instead of inputUri.
            [
                new BinaryFileValue(
                    [
                        'path' => __FILE__,
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                        'uri' => 'http://some/file/here',
                    ]
                ),
                [
                    'id' => null,
                    'inputUri' => __FILE__,
                    'path' => __FILE__,
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                    'uri' => 'http://some/file/here',
                ],
            ],
            // BC with 5.2 (EZP-22808). Id can be used as input instead of inputUri.
            [
                new BinaryFileValue(
                    [
                        'id' => __FILE__,
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                        'uri' => 'http://some/file/here',
                    ]
                ),
                [
                    'id' => null,
                    'inputUri' => __FILE__,
                    'path' => __FILE__,
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                    'uri' => 'http://some/file/here',
                ],
            ],
            // BC with 5.2 (EZP-22808). Id is recognized as such if not pointing to existing file.
            [
                new BinaryFileValue(
                    [
                        'id' => 'application/asdf1234.pdf',
                        'fileName' => 'asdf1234.pdf',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'application/pdf',
                        'uri' => 'http://some/file/here',
                    ]
                ),
                [
                    'id' => 'application/asdf1234.pdf',
                    'inputUri' => null,
                    'path' => null,
                    'fileName' => 'asdf1234.pdf',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'application/pdf',
                    'uri' => 'http://some/file/here',
                ],
            ],
        ];
    }

    /**
     * Provide input to fromHash() method.
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to fromHash(), 2. The expected return value from fromHash().
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          null,
     *          null
     *      ),
     *      array(
     *          array(
     *              'id' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ),
     *          new BinaryFileValue( array(
     *              'id' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ) )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInputForFromHash()
    {
        return [
            [
                null,
                new BinaryFileValue(),
            ],
            [
                [
                    'id' => 'some/file/here',
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                ],
                new BinaryFileValue(
                    [
                        'id' => 'some/file/here',
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                    ]
                ),
            ],
            // BC with 5.0 (EZP-20948). Path can be used as input instead of inputUri.
            [
                [
                    'path' => 'some/file/here',
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                ],
                new BinaryFileValue(
                    [
                        'id' => 'some/file/here',
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                    ]
                ),
            ],
            // BC with 5.2 (EZP-22808). Id can be used as input instead of inputUri.
            [
                [
                    'id' => __FILE__,
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                ],
                new BinaryFileValue(
                    [
                        'id' => null,
                        'inputUri' => __FILE__,
                        'path' => __FILE__,
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                    ]
                ),
            ],
            [
                [
                    'id' => __FILE__,
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                    'uri' => 'some_uri_acquired_from_SPI',
                    'route' => 'some_route',
                ],
                new BinaryFileValue(
                    [
                        'id' => null,
                        'inputUri' => __FILE__,
                        'path' => __FILE__,
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                        'uri' => '__GENERATED_URI__',
                    ]
                ),
            ],
            [
                [
                    'id' => __FILE__,
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                    'uri' => 'some_uri_acquired_from_SPI',
                    'route' => 'some_route',
                    'route_parameters' => [
                        'any_param' => true,
                    ],
                ],
                new BinaryFileValue(
                    [
                        'id' => null,
                        'inputUri' => __FILE__,
                        'path' => __FILE__,
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                        'uri' => '__GENERATED_URI_WITH_PARAMS__',
                    ]
                ),
            ],
            // @todo: Provide upload struct (via REST)!
        ];
    }

    protected function provideFieldTypeIdentifier()
    {
        return 'ezbinaryfile';
    }

    public function provideDataForGetName(): array
    {
        return [
            [new BinaryFileValue(), [], 'en_GB', ''],
            [new BinaryFileValue(['fileName' => 'sindelfingen.jpg']), [], 'en_GB', 'sindelfingen.jpg'],
        ];
    }

    public function provideValidDataForValidate()
    {
        return [
            [
                [
                    'validatorConfiguration' => [
                        'FileSizeValidator' => [
                            'maxFileSize' => 1,
                        ],
                    ],
                ],
                new BinaryFileValue(
                    [
                        'id' => 'some/file/here',
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                    ]
                ),
            ],
        ];
    }

    public function provideInvalidDataForValidate()
    {
        return [
            // File is too large
            [
                [
                    'validatorConfiguration' => [
                        'FileSizeValidator' => [
                            'maxFileSize' => 0.01,
                        ],
                    ],
                ],
                new BinaryFileValue(
                    [
                        'id' => 'some/file/here',
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 150000,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                    ]
                ),
                [
                    new ValidationError(
                        'The file size cannot exceed %size% byte.',
                        'The file size cannot exceed %size% bytes.',
                        [
                            '%size%' => 0.01,
                        ],
                        'fileSize'
                    ),
                ],
            ],

            // file extension is in blacklist
            [
                [
                    'validatorConfiguration' => [
                        'FileSizeValidator' => [
                            'maxFileSize' => 1,
                        ],
                    ],
                ],
                new BinaryFileValue(
                    [
                        'id' => 'phppng.php',
                        'fileName' => 'phppng.php',
                        'fileSize' => 'phppng.php',
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                    ]
                ),
                [
                    new ValidationError(
                        'A valid file is required. Following file extensions are on the blacklist: %extensionsBlackList%',
                        null,
                        ['%extensionsBlackList%' => implode(', ', $this->blackListedExtensions)],
                        'fileExtensionBlackList'
                    ),
                ],
            ],

            // file is an image file but filename ends with .PHP (upper case)
            [
                [
                    'validatorConfiguration' => [
                        'FileSizeValidator' => [
                            'maxFileSize' => 1,
                        ],
                    ],
                ],
                new BinaryFileValue(
                    [
                        'id' => 'phppng.PHP',
                        'fileName' => 'phppng.PHP',
                        'fileSize' => 'phppng.PHP',
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                    ]
                ),
                [
                    new ValidationError(
                        'A valid file is required. Following file extensions are on the blacklist: %extensionsBlackList%',
                        null,
                        ['%extensionsBlackList%' => implode(', ', $this->blackListedExtensions)],
                        'fileExtensionBlackList'
                    ),
                ],
            ],
        ];
    }

    private function getRouteAwarePathGenerator(): RouteAwarePathGenerator
    {
        $mock = $this->createMock(RouteAwarePathGenerator::class);
        $mock->method('generate')
            ->willReturnCallback(static function (string $route, array $routeParameters = []): string {
                if ($routeParameters) {
                    return '__GENERATED_URI_WITH_PARAMS__';
                }

                return '__GENERATED_URI__';
            });

        return $mock;
    }
}

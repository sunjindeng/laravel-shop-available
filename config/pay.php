<?php

return [
    'alipay' => [
        'app_id'         => '2016091300503553',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvTurQhEMw+mQ5fxtxJfdmIKgvmivsQMZ1p1H2pVFFETZxT/TZciN+MOFTvNUu1rVs/b/02iQu3+mB19hLeIBXi5MhaI43XunwuI4nNBXxrr16nxoU9FWFd1Jn1+oY07yJKwrcdj5QEJoWWivNBuoPeKg9BwMF9iPsYjpp+JcBAU4FwZZC2byaCVJF3v2WVRz6IQwf8wGXYpUpUlvkbWs5Cj0YvzPyKSS0zvQqgk5Q6DfiphhVI0datYkHarbeU2DV4FqdtZmQmAfzm+tjd57VltsLuUOfWmwENSmyGT9LLWOxeIE22kwuYT5PLzeG+xLHyHO9CAdA7HpXNYoVPbRfQIDAQAB',
        'private_key'    => 'MIIEpAIBAAKCAQEAmlh+ddkcNdNxPXq2IWGq8xV/j9Lf/LXMKYuMrouEpe/k05PuHdI+SzAZsplgy+zo558S4QJquZjeWHgv5G+Wi6m/QNKzlJmPuthLm+wXtTqbhRKRXG+PmOesGauait/58BYe1We65zcGUW4+2hurJX8zj3d56VYYWDdRcThqqgO18jey9JC0z/wJT5JKdCYBo+m9udbgqu7decYADuui4WNSXUgMSgwoF9mJr+fvuBPAcckvQ1WoI2eI6yYuPWEuVYyQYwWlkquhEuh7eEAg/OZo4kG42o7+oXsWEasZKcpWEs1V/rOIAit+aC4sJC0KZ9hTbhXrgMYiJxCQUWuQeQIDAQABAoIBAFWiLZ+i8qTmiFiM4coJjzhJQDoCzKVAFGMkesxIujL6s2G0HJPNFyTOLiPKN0tNSTdhKa9PkPkiWJKLND8wrj0U0/jaLiqPPJB0+xYqWkkBmFGvqJi5iNlUCEdBz3+nnttW2oqaf7dS7x105khKu50VQv0hU7FjRCSFpJkFrSBgCwVX+gC/0MPq158rlT6Ix7Bf19jErrRwjKtfy6xUbZgGWRTa1NmfAgul09sIyV/S25cIueoxsB/BmZNjsG5NOOhH12lg25tGc0nO19fpxU804cm89hx/QGe4+V2+g0+PGkgpt86hWd/DyKmD9ax0czPQIS472kffdmxPX5ddVRECgYEA230OLShShox7lLxm+BBN9FtHTqxjyQGwkGjMQ9MTr2J4rd3LsZUMdgZcLcZIjY0Xigpb7IuM/WpCLiX71m/bUOluSQsc6Pdv8aAhCvojpIuA5BPF+48jv0KHRoynDmoVudhYdNkX7ii1N9c6xb1JzhE0b7JE066cD+Fxr+29TrUCgYEAtAVS3n8AswkW5KjjRC5y1DK8GhjxaPFkiYt6JZZK0xz/YEnA0X3H9Uzym6++vTC8VL/W3JFU1YwqgYHT/gzrCRy5ZbqLdtOymHktjrSF5vAYou21tHvTsbdNE54NfW5kSUSk6Pk3XmQ7nj8iu2YpXl7TcsJSbHFaIoXm4zbnUTUCgYAnPpb4UAthb2DxWTZ4CEJH/MIlOQGmgRzW1UYgom8UnuDBBbVUDThrKfv1W0zBsoaQ8gU5qEIPVZ57reL3lox6TSKhANGsfgGQDHecBNm2pBLptPdjYVac7gJOBPwhwaoczqgSTHbQ6Ipub5dcn9nAdKcRiahwyPmyhTbUpAeDOQKBgQChvvbKOspiEpSjNqucIy/BQSSJPkgJxnpSqyNAil0IelFqLlo04BK9cDm7cJisXBGbBBMFcv8KMDowynmzFeBH+6sIoCeWyHb7UEWCpuh4qtk36uUMGQYH824pZiUwwfMRcb/KI8F/6gc3E/sc7ZnV1NWfksGD9gTKaA0fscD80QKBgQCLR1IislXLCnMJhBfh9iwWOJA50mXsYUYw0LgbE0Nl2p9FCMbr5o7J2fESHrLNAA0QzQiPERUTvDRqQDBHcQ4up+68M80VolCPBmKkcCakhVi6XDTQOYDsNGlQ2cyWZz4Ng4scU2QGvHOL89fk/iXKXfwleqEf5jntflKLJMjVEg==',
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ],
    ],

    'wechat' => [
        'app_id'      => '',
        'mch_id'      => '',
        'key'         => '',
        'cert_client' => '',
        'cert_key'    => '',
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ],
];

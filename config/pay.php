<?php

return [
    'alipay' => [
        'app_id'         => '2016091300503553',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvTurQhEMw+mQ5fxtxJfdmIKgvmivsQMZ1p1H2pVFFETZxT/TZciN+MOFTvNUu1rVs/b/02iQu3+mB19hLeIBXi5MhaI43XunwuI4nNBXxrr16nxoU9FWFd1Jn1+oY07yJKwrcdj5QEJoWWivNBuoPeKg9BwMF9iPsYjpp+JcBAU4FwZZC2byaCVJF3v2WVRz6IQwf8wGXYpUpUlvkbWs5Cj0YvzPyKSS0zvQqgk5Q6DfiphhVI0datYkHarbeU2DV4FqdtZmQmAfzm+tjd57VltsLuUOfWmwENSmyGT9LLWOxeIE22kwuYT5PLzeG+xLHyHO9CAdA7HpXNYoVPbRfQIDAQAB',
        'private_key'    => 'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCAxcPB697ukH1CGEuKyccyW7SyU7B84hB9trajyvInexVA4M7EGfAtYlg5cPsUef2I1ZJ+9ztiOaN8u9qw4jyuz6XHoDo3YEm2TEMlkdq0M/R+TXgNFxkPY3s+7AVf8e7ThR/Bg7+JbtzrEoibqywujQq9oJ58bXDD+qcuQpTWnl5wsDrkoR7Q6XP6Pi2zRBXr7MS+JvhS4a9ZX6mE32lxjlz1wH+3XcezvJlHv+cnad8X8l+QIWh3dUzLdNpXYdpPDmqCVUpEw7HcopvJdq7ULTb9PequZP6aMqUsh6X+16o2SSN2iqLsH3X++/whN16pPx7Vd+VTNqm1w7aEHUS7AgMBAAECggEAdDKANz9g1cJ51KdZmy4CWjWYMwYDjlzrZpT174zUv7136ygwS5HgQQOp+oMxfUOq/MtDOuBarIK4PHjCmLQ8770dqamyfn+bZC0itgcL42myaDn9Z0p9aX5qGtgc7XvA9wr8IcGBl0D7hf8eV+G4p3TLyVrjSAH1LFLEgqC/73yFLIE91lp/HHJ0AgTmewJY2vL0bWA0kfGLNkB2/TvzsGjd4oV0VFxlaXrP7YxYAgib38EjAdCzAbmE8YRKDj/6A73I2RKiqsErvajYbI3+kMuadD74MIn3xk9WC9iIx3ieepJK6ohtfFj7GllKiU9nAWECMijSvB8CEfKG85//wQKBgQDHzkSQ3nr/0XD3UBAyiY/YYGbjxR+jEgzKVZjjWp6SeDGshl0VBv3n+RnCCrNl42RGxoLvHHbbeOSGtkZ3aHskNYglUq/Qo66fpeina8XPN1ZituSzhsUtESUvibgKNxy2OtMjRc8xGwkQY712cDoG0N5eo5myAg/07SlxlQmfYQKBgQCk/TSB/MwGffoVpI+hh0iHTexQCwVyIp6YnCkazjze1XbTdXS/AR1VvZZjlc7eAdEchsNr621nxth3dMn5c79QBTkBRAvqg1j7NMwnLEQEhaPDmJiqwtHzU2dV0maWAETLi35/DyEqbefDghdoEN33NzzchuR15oj59uXf3rjlmwKBgQCvRPiOoCsDVyUiPQBIZLVjGIWJDfVHpeDEaLvZzDdHwtnIPoFOnbiDEdePiLWADi17jE11FOIKegz1QtWjj2peA/tuyg7iFYNsFix1GKwHrsI/i7Io7XLvqpeVENj5VQkBra5ixa0PpWiZEPU0/RNZwUUffaGoLfjzYEs3kIZFYQKBgBmVMXkea7xt+EM22xae1X41dPkMXHHQMGtVe4IujH24983eHpYnZBcRaKpUZllCStxzgQXQ4Yv/5zzOnSrS6c1MogQyOu7IIxoAm89PK9TPrA7+MF36C3gBbLp//2wu6nvtS1YEUI41sIQ+PcbjJqfYptVDDVXCw2aPBCHsdRsVAoGACtHMuBLj+D3OAGS2g7Z0y7+rmgGKmJ3iWkFaYmKldoczV6DfX/0hyh7L+NDQOsO2iZobHtLRIY5/IoQILlJWMxN2NaYdJkJVJlJOl6TMc8Q/uzSTbNHBvL8jJ2ouFM0g/gvl6P5mVQva4lUuWjYOrSodVY7kDRTCFUrL7ndnnr0=',
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

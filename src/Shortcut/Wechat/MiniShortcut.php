<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Wechat;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\NormalizeParamsPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\AddPayloadSignaturePlugin as V2AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Pay\Mini\InvokePlugin as V2InvokePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Pay\Mini\PayPlugin as V2PayPlugin;
use Yansongda\Pay\Plugin\Wechat\V2\VerifySignaturePlugin as V2VerifySignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\InvokePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\PayPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\VerifySignaturePlugin;

use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\is_wechat_v2;

class MiniShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        if (is_wechat_v2(get_provider_config('wechat', $params))) {
            return [
                StartPlugin::class,
                NormalizeParamsPlugin::class,
                V2PayPlugin::class,
                V2AddPayloadSignaturePlugin::class,
                AddPayloadBodyPlugin::class,
                AddRadarPlugin::class,
                V2InvokePlugin::class,
                V2VerifySignaturePlugin::class,
                ResponsePlugin::class,
                ParserPlugin::class,
            ];
        }

        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            PayPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            InvokePlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}

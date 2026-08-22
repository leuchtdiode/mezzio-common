<?php
declare(strict_types=1);

namespace CommonTest\Dto\Save\Fixture\Thing;

use Common\Dto\BaseDto;
use CommonTest\Dto\Save\Fixture\Attachment\Dto as AttachmentDto;

/**
 * @method string getName()
 * @method AttachmentDto|null getAttachment()
 */
class Dto extends BaseDto
{
}

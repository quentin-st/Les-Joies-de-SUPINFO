<?php

namespace LjdsBundle\Entity;

abstract class GifState
{
	const SUBMITTED = 0;
	const ACCEPTED = 1;
	const REFUSED = 2;
	const PUBLISHED = 3;

	public static function fromName($name)
	{
		switch ($name) {
			case 'submitted':	return GifState::SUBMITTED;
			case 'accepted': 	return GifState::ACCEPTED;
			case 'refused':		return GifState::REFUSED;
			case 'published':	return GifState::PUBLISHED;
			default: 			return -1;
		}
	}
}

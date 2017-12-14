/*
** Zabbix
** Copyright (C) 2001-2017 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

#include "zbxmocktest.h"
#include "zbxmockdata.h"
#include "zbxmockassert.h"
#include "zbxmockutil.h"

#include "common.h"

void	zbx_mock_get_parameter_string(const char *path, const char **value)
{
	zbx_mock_error_t	err;
	zbx_mock_handle_t	handle;

	if (ZBX_MOCK_SUCCESS != (err = zbx_mock_parameter(path, &handle)) ||
			ZBX_MOCK_SUCCESS != (err = zbx_mock_string(handle, value)))
	{
		fail_msg("Cannot read parameter at \"%s\": %s", path, zbx_mock_error_string(err));
	}
}

void	zbx_mock_get_object_member_string(zbx_mock_handle_t object, const char *name, const char **value)
{
	zbx_mock_error_t	err;
	zbx_mock_handle_t	handle;

	if (ZBX_MOCK_SUCCESS != (err = zbx_mock_object_member(object, name, &handle)) ||
			ZBX_MOCK_SUCCESS != (err = zbx_mock_string(handle, value)))
	{
		fail_msg("Cannot read object member \"%s\": %s", name, zbx_mock_error_string(err));
	}
}

void	zbx_mock_get_parameter_handle(const char *path, zbx_mock_handle_t *handle)
{
	zbx_mock_error_t	err;

	if (ZBX_MOCK_SUCCESS != (err = zbx_mock_parameter(path, handle)))
		fail_msg("Cannot read parameter at \"%s\": %s", path, zbx_mock_error_string(err));
}

# -*- coding: utf-8 -*-
# Generated by Django 1.9.2 on 2016-02-19 10:31
from __future__ import unicode_literals

import datetime
from django.db import migrations, models
from django.utils.timezone import utc


class Migration(migrations.Migration):

    dependencies = [
        ('case', '0003_auto_20160218_2212'),
    ]

    operations = [
        migrations.AddField(
            model_name='customuser',
            name='updated_at',
            field=models.DateTimeField(auto_now=True, default=datetime.datetime(2016, 2, 19, 10, 31, 5, 518347, tzinfo=utc)),
            preserve_default=False,
        ),
    ]

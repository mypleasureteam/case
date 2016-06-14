# -*- coding: utf-8 -*-
# Generated by Django 1.9.2 on 2016-05-21 14:42
from __future__ import unicode_literals

import datetime
from django.db import migrations, models
from django.utils.timezone import utc
import django.utils.timezone


class Migration(migrations.Migration):

    dependencies = [
        ('case', '0015_auto_20160521_1633'),
    ]

    operations = [
        migrations.AddField(
            model_name='userblockrelationship',
            name='since',
            field=models.DateTimeField(auto_now_add=True, default=datetime.datetime(2016, 5, 21, 14, 41, 59, 759452, tzinfo=utc)),
            preserve_default=False,
        ),
        migrations.AddField(
            model_name='usercollectionblockrelationship',
            name='since',
            field=models.DateTimeField(auto_now_add=True, default=datetime.datetime(2016, 5, 21, 14, 42, 8, 91468, tzinfo=utc)),
            preserve_default=False,
        ),
        migrations.AddField(
            model_name='usercollectionfollowrelationship',
            name='since',
            field=models.DateTimeField(auto_now_add=True, default=datetime.datetime(2016, 5, 21, 14, 42, 14, 112104, tzinfo=utc)),
            preserve_default=False,
        ),
        migrations.AddField(
            model_name='userfollowrelationship',
            name='since',
            field=models.DateTimeField(auto_now_add=True, default=django.utils.timezone.now),
            preserve_default=False,
        ),
    ]

# -*- coding: utf-8 -*-
# Generated by Django 1.9.2 on 2016-07-20 08:47
from __future__ import unicode_literals

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('case', '0024_auto_20160623_0952'),
    ]

    operations = [
        migrations.AddField(
            model_name='video',
            name='scale',
            field=models.CharField(default='normal', max_length=20),
        ),
    ]

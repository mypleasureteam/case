# -*- coding: utf-8 -*-
# Generated by Django 1.9.2 on 2016-03-17 08:16
from __future__ import unicode_literals

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('case', '0007_auto_20160309_1913'),
    ]

    operations = [
        migrations.AlterField(
            model_name='mediaqueue',
            name='created_at',
            field=models.DateTimeField(auto_now_add=True),
        ),
        migrations.AlterField(
            model_name='mediaqueue',
            name='status',
            field=models.CharField(blank=True, default='pending', max_length=30),
        ),
        migrations.AlterField(
            model_name='mediastore',
            name='created_at',
            field=models.DateTimeField(auto_now_add=True),
        ),
    ]
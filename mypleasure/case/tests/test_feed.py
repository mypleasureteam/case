"""CASE (MyPleasure API) tests for Feed."""
from django.test import TestCase
from django.contrib.auth import get_user_model
from rest_framework.test import APIClient
from case.models import Video


class FeedTestCase(TestCase):
    """Test case for Feed."""

    def setUp(self):
        """Set up test."""
        self.url_normal1 = '/api/v1/feed/'
        self.url_normal2 = '/api/v1/feed/normal'
        self.url_naughty = '/api/v1/feed/naughty'

        self.client = APIClient()

        self.u1 = get_user_model().objects.create_user('morgane', 'azertyuiop')
        self.u2 = get_user_model().objects.create_user('marion', 'azertyuiop')

        self.pb_coll = self.u1.collections.first()
        self.pv_coll = self.u1.collections.create(name='pv', is_private=True)
        self.pb_vid_nm = self.pb_coll.videos.create(
            hash='pbnmh', title='pbnmt', poster='pbnmp',
            original_url='http://aa.cc', embed_url='http://aa.cc',
            duration='--:--:--', is_naughty=False
        )
        self.pv_vid_nm = self.pv_coll.videos.create(
            hash='pvnmh', title='pvnmt', poster='pvnmp',
            original_url='http://aa.cc', embed_url='http://aa.cc',
            duration='--:--:--', is_naughty=False
        )
        self.pb_vid_nt = self.pb_coll.videos.create(
            hash='pbnth', title='pbntt', poster='pbntp',
            original_url='http://aa.cc', embed_url='http://aa.cc',
            duration='--:--:--', is_naughty=True
        )
        self.pv_vid_nt = self.pv_coll.videos.create(
            hash='pvnth', title='pvntt', poster='pvntp',
            original_url='http://aa.cc', embed_url='http://aa.cc',
            duration='--:--:--', is_naughty=True
        )

        response = self.client.post('/api/v1/auth/login/', {
            'username': 'morgane', 'password': 'azertyuiop'
        })
        self.token = response.data['token']
        self.auth = 'Bearer {0}'.format(self.token)

    def test_prerequisites(self):
        """Test prerequisite for Feed tests."""
        self.assertEqual(self.u1.collections.all().count(), 2)
        self.assertEqual(len(self.u1.videos), 4)
        self.assertTrue(self.pv_coll.is_private)
        self.assertFalse(self.pb_coll.is_private)
        self.assertEqual(Video.objects.filter(is_naughty=False).count(), 2)
        self.assertEqual(Video.objects.filter(is_naughty=True).count(), 2)

    def test_feed_requires_authentication(self):
        """Test Feed requires authentication."""
        r1 = self.client.get(self.url_normal1)
        r2 = self.client.get(self.url_normal2)
        r3 = self.client.get(self.url_naughty)
        self.assertEqual(r1.status_code, 403)
        self.assertEqual(r2.status_code, 403)
        self.assertEqual(r3.status_code, 403)

    def test_normal_feed_only_returns_normal_videos(self):
        """Test GET /api/v1/feed/?normal?/? returns normal videos."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')

        r1 = self.client.get(self.url_normal1)
        self.assertEqual(len(r1.data['videos']), 2)
        self.assertEqual(r1.data['videos'][0]['title'], 'pbnmt')
        self.assertEqual(r1.data['videos'][1]['title'], 'pvnmt')
        self.assertFalse(r1.data['videos'][0]['is_naughty'])
        self.assertFalse(r1.data['videos'][1]['is_naughty'])

        r2 = self.client.get(self.url_normal2)
        self.assertEqual(len(r2.data['videos']), 2)
        self.assertEqual(r1.data['videos'][0]['title'], 'pbnmt')
        self.assertEqual(r1.data['videos'][1]['title'], 'pvnmt')
        self.assertFalse(r1.data['videos'][0]['is_naughty'])
        self.assertFalse(r1.data['videos'][1]['is_naughty'])

    def test_naughty_feed_only_returns_naughty_videos(self):
        """Test GET /api/v1/feed/naughy returns naughty videos."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')
        r = self.client.get(self.url_naughty)
        self.assertEqual(len(r.data['videos']), 2)
        self.assertEqual(r.data['videos'][0]['title'], 'pbntt')
        self.assertEqual(r.data['videos'][1]['title'], 'pvntt')
        self.assertTrue(r.data['videos'][0]['is_naughty'])
        self.assertTrue(r.data['videos'][1]['is_naughty'])

    def test_normal_feed_hides_ownership_for_private_collections(self):
        """Test GET /api/v1/feed/?normal?/? hides owners of PV collections."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')

        r1 = self.client.get(self.url_normal1)
        r2 = self.client.get(self.url_normal2)

        self.assertIn('owner', r1.data['videos'][0])
        self.assertNotIn('owner', r1.data['videos'][1])
        self.assertIn('owner', r2.data['videos'][0])
        self.assertNotIn('owner', r2.data['videos'][1])

    def test_naughty_feed_hides_ownership_for_private_collections(self):
        """Test /api/v1/feed/naughty hides owners for PV collections."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')
        r = self.client.get(self.url_naughty)
        self.assertIn('owner', r.data['videos'][0])
        self.assertNotIn('owner', r.data['videos'][1])

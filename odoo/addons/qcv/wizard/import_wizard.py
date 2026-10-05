import requests
from odoo import Command, fields, models
from odoo.exceptions import UserError

class CvImportWizard(models.TransientModel):
    _name = 'cv.import.wizard'
    _description = 'Import position results'

    base_url = fields.Char(required=True, default='https://your-app.example.com')
    api_token = fields.Char(required=True)

    def action_import(self):
        self.ensure_one()
        try:
            r = requests.get(f"{self.base_url.rstrip('/')}/api/v1/position-results",
                             headers={'Authorization': f'Bearer {self.api_token.strip()}'}, timeout=15)
            r.raise_for_status()
            data = r.json()
        except (requests.RequestException, ValueError) as e:
            raise UserError(f"Import failed: {e}")

        Position = self.env['qcv.position'].sudo() 
        vals = {'title': data['title'], 'source_id': data['id'],
                'cv_count': data.get('cv_count', 0), 'imported_at': fields.Datetime.now()}
        pos = Position.search([('source_id', '=', data['id'])], limit=1)
        if pos:
            pos.write(vals)
            pos.attribute_ids.unlink()
        else:
            pos = Position.create(vals)
        pos.attribute_ids = [Command.create({
            'title': a['title'], 'attr_type': a['type'], 'answers_count': a.get('count', 0),
            'avg_value': a.get('avg', 0), 'min_value': a.get('min', 0), 'max_value': a.get('max', 0),
            'top_values': ', '.join(f"{t['value']} ({t['count']})" for t in a.get('top_values', [])),
        }) for a in data['attributes']]
        return {'type': 'ir.actions.act_window', 'res_model': 'cv.position',
                'res_id': pos.id, 'view_mode': 'form'}